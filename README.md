# Value Chain Markets

A web application for analyzing and visualizing industry value chains. The system generates comprehensive supply chain analysis, business opportunities, pain points, and optimization recommendations using Azure OpenAI Service.

## Features

- Industry value chain visualization using D3.js
- Supply chain analysis powered by Azure OpenAI
- Business opportunity identification
- Pain points analysis
- Optimization recommendations
- Interactive visualization with drag-and-drop support

## Prerequisites

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Azure OpenAI Service API access

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file and configure your settings:
   ```bash
   cp .env.example .env
   ```

4. Configure your database and Azure OpenAI credentials in the `.env` file

5. Import the database schema:
   ```bash
   mysql -u your_username -p < database/schema.sql
   ```

6. Start the PHP development server:
   ```bash
   php -S localhost:8000
   ```

7. Open your browser and navigate to `http://localhost:8000`

## Usage

1. Enter an industry, business, or product name in the input field
2. Click "Analyze" to generate the value chain analysis
3. Explore the interactive visualization
4. Review business opportunities, pain points, and optimization tips in the analysis panel

## Technology Stack

- Backend: PHP, MySQL
- Frontend: HTML, JavaScript (jQuery, D3.js), CSS (Bootstrap)
- API: Azure OpenAI Service
- Database: MySQL

## License

MIT License
