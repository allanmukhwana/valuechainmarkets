$(document).ready(function() {
    let width = $('#treemap').width();
    let height = $('#treemap').height();
    let currentAnalysis;

    $('#analyzeBtn').click(function() {
        const industry = $('#industryInput').val();
        if (!industry) {
            alert('Please enter an industry, business, or product name');
            return;
        }

        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Analyzing...');

        // Call backend API
        $.ajax({
            url: 'api/analyze.php',
            method: 'POST',
            data: { industry: industry },
            success: function(response) {
                updateTreemap(response.analysis);
                currentAnalysis = response.analysis;
            },
            error: function(xhr, status, error) {
                alert('Error analyzing industry: ' + error);
            },
            complete: function() {
                $('#analyzeBtn').prop('disabled', false).text('Analyze');
            }
        });
    });

    function updateTreemap(analysis) {
        // Clear previous visualization
        d3.select('#treemap').html('');

        // Prepare data for treemap
        const data = {
            name: "Value Chain Analysis",
            children: [
                {
                    name: "Opportunities",
                    children: analysis.opportunities.map(opp => ({ name: opp, value: 1, category: 'opportunities' }))
                },
                {
                    name: "Pain Points",
                    children: analysis.painPoints.map(pain => ({ name: pain, value: 1, category: 'pain-points' }))
                },
                {
                    name: "Optimization Tips",
                    children: analysis.optimizationTips.map(tip => ({ name: tip, value: 1, category: 'optimization' }))
                }
            ]
        };

        // Create treemap layout
        const treemap = d3.treemap()
            .size([width, height])
            .padding(1)
            .round(true);

        // Create hierarchy
        const root = d3.hierarchy(data)
            .sum(d => d.value)
            .sort((a, b) => b.value - a.value);

        // Generate treemap layout
        treemap(root);

        // Create container
        const svg = d3.select('#treemap')
            .append('div')
            .style('position', 'relative')
            .style('width', width + 'px')
            .style('height', height + 'px');

        // Create cells
        const cell = svg.selectAll('div')
            .data(root.leaves())
            .enter().append('div')
            .attr('class', d => `treemap-cell ${d.data.category}`)
            .style('left', d => d.x0 + 'px')
            .style('top', d => d.y0 + 'px')
            .style('width', d => (d.x1 - d.x0) + 'px')
            .style('height', d => (d.y1 - d.y0) + 'px');

        // Add text to cells
        cell.append('div')
            .style('padding', '5px')
            .style('word-wrap', 'break-word')
            .text(d => d.data.name);

        // Add tooltips
        cell.attr('title', d => d.data.name)
            .attr('data-bs-toggle', 'tooltip')
            .attr('data-bs-placement', 'top');

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Handle window resize
    $(window).resize(function() {
        width = $('#treemap').width();
        height = $('#treemap').height();
        // Update treemap if data exists
        if ($('#treemap').children().length > 0) {
            updateTreemap(currentAnalysis);
        }
    });
});
