<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get client IP address
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

try {
    $db = getDb();
    
    // Check IP limits
    $today = date('Y-m-d');
    $stmt = $db->prepare('SELECT * FROM ip_tracking WHERE ip_address = ? AND first_request_of_day = ?');
    $stmt->execute([$ip_address, $today]);
    $tracking = $stmt->fetch();
    
    if ($tracking) {
        if ($tracking['request_count'] >= 10) {
            http_response_code(429);
            echo json_encode(['error' => 'Daily limit exceeded. Maximum 10 analyses per day.']);
            exit;
        }
        
        // Update request count
        $stmt = $db->prepare('UPDATE ip_tracking SET request_count = request_count + 1, last_request = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$tracking['id']]);
    } else {
        // First request of the day
        $stmt = $db->prepare('INSERT INTO ip_tracking (ip_address, first_request_of_day) VALUES (?, ?)');
        $stmt->execute([$ip_address, $today]);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    if (!isset($input['industry'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Industry parameter is required']);
        exit;
    }

    $industry = $input['industry'];
    
    // Create Azure OpenAI API client using GuzzleHttp
    $client = new GuzzleHttp\Client();
    
    // Prepare the prompt for value chain analysis
    $prompt = "Please analyze the value chain for the {$industry} industry and provide a structured response in the following format:

STAGES:
List the stages from raw materials to end customer in this exact order:
1. Raw Materials
2. Suppliers
3. Manufacturers
4. Distributors
5. Retailers
6. Customers

For each stage provide:
- stage: [exact stage name from the list above]
- description: [brief description of the stage's role]
- order: [number 1-6 matching the list above]

OPPORTUNITIES:
- List key business opportunities, one per line
- Each line should start with '**opportunity:**'

PAIN POINTS:
- List major pain points, one per line
- Each line should start with '**pain point:**'

OPTIMIZATION TIPS:
- List optimization recommendations, one per line
- Each line should start with '**optimization:**'

Please be specific and concise in your analysis.";
    
    // Call Azure OpenAI API
    $response = $client->post($config['azure']['endpoint'] . '/openai/deployments/' . 
                            $config['azure']['deployment'] . '/chat/completions?api-version=' . 
                            $config['azure']['api_version'], [
        'headers' => [
            'api-key' => $config['azure']['key'],
            'Content-Type' => 'application/json'
        ],
        'json' => [
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert in industry value chain analysis.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 800
        ]
    ]);
    
    $result = json_decode($response->getBody(), true);
    
    // Process the API response and structure the data
    $analysis = processAnalysis($result['choices'][0]['message']['content']);
    
    // Store in database
    storeAnalysis($db, $industry, $analysis);
    
    echo json_encode([
        'valueChain' => [
            'nodes' => $analysis['nodes'],
            'links' => $analysis['links']
        ],
        'stages' => $analysis['stages'],
        'analysis' => [
            'opportunities' => $analysis['opportunities'],
            'painPoints' => $analysis['painPoints'],
            'optimizationTips' => $analysis['optimizationTips']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function processAnalysis($content) {
    $opportunities = [];
    $painPoints = [];
    $optimizationTips = [];
    $stages = [];
    
    // Define valid stages and their order
    $validStages = [
        'Raw Materials' => ['order' => 1, 'type' => 'raw_materials'],
        'Suppliers' => ['order' => 2, 'type' => 'suppliers'],
        'Manufacturers' => ['order' => 3, 'type' => 'manufacturers'],
        'Distributors' => ['order' => 4, 'type' => 'distributors'],
        'Retailers' => ['order' => 5, 'type' => 'retailers'],
        'Customers' => ['order' => 6, 'type' => 'customers']
    ];
    
    // Split content into sections
    $sections = explode("\n", $content);
    $currentSection = '';
    
    foreach ($sections as $key => $line) {
        $line = trim($line);
        
        // Skip empty lines
        if (empty($line)) continue;
        
        // Determine section
        if (stripos($line, 'STAGES:') !== false) {
            $currentSection = 'stages';
            continue;
        } elseif (stripos($line, 'OPPORTUNITIES:') !== false) {
            $currentSection = 'opportunities';
            continue;
        } elseif (stripos($line, 'PAIN POINTS:') !== false) {
            $currentSection = 'painPoints';
            continue;
        } elseif (stripos($line, 'OPTIMIZATION TIPS:') !== false) {
            $currentSection = 'optimization';
            continue;
        }
        
        // Process line based on current section
        if ($currentSection === 'stages') {
            // Check if line contains a stage number (1-6)
            if (preg_match('/^[1-6]\.\s*([\w\s]+)$/', $line, $matches)) {
                $stageName = trim($matches[1]);
                if (isset($validStages[$stageName])) {
                    $stage = [
                        'name' => $stageName,
                        'order' => $validStages[$stageName]['order'],
                        'node_type' => $validStages[$stageName]['type'],
                        'description' => '' // Will be updated if description is found
                    ];
                    
                    // Look ahead for description in next lines
                    for ($i = 1; $i <= 3; $i++) {
                        if (isset($sections[$key + $i])) {
                            $nextLine = trim($sections[$key + $i]);
                            if (!empty($nextLine) && 
                                !preg_match('/^[1-6]\./', $nextLine) && 
                                stripos($nextLine, 'OPPORTUNITIES:') === false) {
                                $stage['description'] = $nextLine;
                                break;
                            }
                        }
                    }
                    
                    $stages[] = $stage;
                }
            }
        } elseif ($currentSection === 'opportunities' && stripos($line, '**') !== false) {
            $opportunities[] = trim(str_replace('**', '', $line));
        } elseif ($currentSection === 'painPoints' && stripos($line, '**') !== false) {
            $painPoints[] = trim(str_replace('**', '', $line));
        } elseif ($currentSection === 'optimization' && stripos($line, '**') !== false) {
            $optimizationTips[] = trim(str_replace('**', '', $line));
        }
    }
    
    // If no stages were found, create default stages
    if (empty($stages)) {
        foreach ($validStages as $name => $info) {
            $stages[] = [
                'name' => $name,
                'order' => $info['order'],
                'node_type' => $info['type'],
                'description' => getDefaultDescription($name)
            ];
        }
    }
    
    // Sort stages by order
    usort($stages, function($a, $b) {
        return ($a['order'] ?? 0) - ($b['order'] ?? 0);
    });
    
    // Create nodes and links for visualization
    $nodes = [];
    $links = [];
    
    foreach ($stages as $stage) {
        $nodes[] = [
            'id' => $stage['node_type'],
            'name' => $stage['name'],
            'description' => $stage['description'],
            'type' => $stage['node_type']
        ];
    }
    
    // Create links between consecutive stages
    for ($i = 0; $i < count($nodes) - 1; $i++) {
        $links[] = [
            'source' => $nodes[$i]['id'],
            'target' => $nodes[$i + 1]['id']
        ];
    }
    
    return [
        'nodes' => $nodes,
        'links' => $links,
        'stages' => $stages,
        'opportunities' => $opportunities,
        'painPoints' => $painPoints,
        'optimizationTips' => $optimizationTips
    ];
}

function getDefaultDescription($stageName) {
    $descriptions = [
        'Raw Materials' => 'Sourcing and extraction of primary resources',
        'Suppliers' => 'Component and material providers',
        'Manufacturers' => 'Production and assembly',
        'Distributors' => 'Logistics and warehousing',
        'Retailers' => 'Point of sale to customers',
        'Customers' => 'End users and consumers'
    ];
    
    return $descriptions[$stageName] ?? '';
}

function storeAnalysis($db, $industry, $analysis) {
    // Store the industry
    $stmt = $db->prepare('INSERT INTO industries (name) VALUES (?)');
    $stmt->execute([$industry]);
    $industryId = $db->lastInsertId();
    
    // Store stages as nodes
    foreach ($analysis['stages'] as $stage) {
        $stmt = $db->prepare('INSERT INTO value_chain_nodes (industry_id, node_type, name, description, stage_order) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $industryId,
            $stage['node_type'],
            $stage['name'],
            $stage['description'],
            $stage['order'] ?? 0
        ]);
    }
}
