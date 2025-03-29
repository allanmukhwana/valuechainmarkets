# Value Chain Markets - Azure AI Developer Hackathon Guide

## Prompts and Guidance for Development with GitHub Copilot and Azure AI

### System Architecture Overview

```
 Help me create a single page application called "Value Chain Markets" that analyzes and visualizes industry value chains. The user will input an industry, business, or product, and the system will generate the entire supply chain, business opportunities, pain points, and optimization tips. I'll use PHP, MySQL, HTML, JS (jQuery and D3.js), and CSS (Bootstrap). The backend will leverage Azure OpenAI Service for analysis.
```

### Database Structure

```
 Let's design a MySQL database schema for a value chain analysis application. I need tables for industries, supply chain stages, business opportunities, pain points, and optimization tips. Each industry should have multiple supply chain stages, and each stage should have its associated opportunities, pain points, and tips.
```

### API Integration with Azure OpenAI

```
 Write a PHP function that calls Azure OpenAI Service to analyze an industry input and generate a complete value chain analysis. The function should take an industry name as input and return structured data about supply chain stages, business opportunities at each stage, pain points, and optimization tips.
```

### Frontend Development

```
 Create a responsive single-page application using Bootstrap for a value chain analysis tool. I need a clean interface with an input form for the industry name, and sections to display the resulting value chain visualization using D3.js, along with tabs or accordions to show business opportunities, pain points, and optimization tips for each stage of the value chain.
```

### D3.js Visualization

```
 Help me create a D3.js visualization that shows a value chain as a horizontal flow diagram. Each node should represent a stage in the supply chain, with arrows connecting them. I want to be able to click on each node to display detailed information about that stage. The visualization should be responsive and adapt to different screen sizes.
```

### User Authentication

```
 Implement a simple user authentication system in PHP that allows users to register, login, and save their value chain analyses for future reference. The system should use secure password hashing and session management.
```

### Deployment Script

```
 Write a deployment script that sets up my Value Chain Markets application on Azure. I need to create a MySQL database in Azure Database for MySQL, deploy the PHP application to Azure App Service, and configure the connection to Azure OpenAI Service.
```

## Tutorial on Setting Up Everything on Azure

### 1. Set Up Azure Resources

1. **Create an Azure Account**
   - Sign up for an Azure account or log in to your existing account
   - Navigate to the Azure Portal (portal.azure.com)

2. **Set Up Azure OpenAI Service**
   - Search for "Azure OpenAI" in the marketplace
   - Create a new Azure OpenAI resource
   - Select a region, pricing tier, and resource group
   - Note your API endpoint and key for later use

3. **Create an Azure Database for MySQL**
   - Search for "Azure Database for MySQL" in the marketplace
   - Create a new MySQL server
   - Configure server name, admin username, and password
   - Set up a firewall rule to allow your IP address
   - Create a new database for your application

4. **Set Up Azure App Service**
   - Search for "App Service" in the marketplace
   - Create a new App Service plan with PHP support
   - Choose a region, pricing tier, and resource group
   - Enable FTP deployment or GitHub integration

### 2. Configure Your Local Development Environment

1. **Install Required Tools**
   - VS Code with GitHub Copilot extension
   - PHP and MySQL local development environment (e.g., XAMPP, WAMP)
   - Git for version control

2. **Set Up GitHub Repository**
   - Create a new GitHub repository for your project
   - Clone it to your local machine
   - Initialize your project structure

3. **Configure Azure Connection in Your Code**
   - Create a config.php file with your Azure credentials
   - Use environment variables or Azure Key Vault for sensitive information

### 3. Deploy to Azure

1. **Deploy Database Schema**
   - Use MySQL Workbench or phpMyAdmin to connect to your Azure MySQL server
   - Run your database creation script

2. **Deploy Application Code**
   - Use VS Code's Azure extension or FTP to upload your code to App Service
   - Configure application settings in the Azure Portal

3. **Set Up Continuous Deployment (Optional)**
   - Configure GitHub Actions or Azure DevOps for CI/CD
   - Set up automated testing and deployment
