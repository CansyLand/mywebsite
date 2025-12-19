# MyWebsite - Portfolio Builder

A PHP-based portfolio website builder with AI-powered content generation using xAI's Grok API.

## Features

- User authentication and registration
- Project/portfolio management
- AI-powered content generation
- Drag-and-drop page builder
- File upload system
- Responsive design

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/mywebsite.git
   cd mywebsite
   ```

2. **Set up environment variables**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` and add your xAI API key:

   ```
   XAI_API_KEY=your-actual-xai-api-key-here
   ```

3. **Configure the application**

   ```bash
   cp config.example.php config.php
   ```

   Edit `config.php` if needed (environment variables are loaded automatically).

4. **Set up the database**

   ```bash
   # Create the SQLite database
   php init.php
   ```

5. **Set up web server**

   - Point your web server to the project root directory
   - Make sure `storage/uploads/` is writable
   - For Apache, the included `.htaccess` handles URL rewriting

6. **Deploy to production (FTP)**

   ```bash
   # Initial setup (already done)
   git ftp init

   # For future deployments, simply run:
   git ftp push
   ```

   This will only upload changed files since the last deployment.
   Production URL: https://parutkin.com/mywebsite/

## Requirements

- PHP 8.0 or higher
- SQLite (included with PHP) or MySQL
- Web server (Apache/Nginx)
- xAI API key for AI features

## Configuration

The application uses environment variables for sensitive configuration. Key settings:

- `XAI_API_KEY`: Your xAI API key
- `BASE_URL`: The base URL for your installation
- Database settings (optional, defaults to SQLite)

## File Structure

```
mywebsite/
├── api/           # API endpoints
├── assets/        # CSS, JS, images
├── database/      # Database files and schema
├── pages/         # PHP page templates
├── src/           # Core PHP classes
├── storage/       # User uploads
├── config.php     # Configuration (not in git)
├── .env.example   # Environment variables template
└── init.php       # Database initialization
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source. Please check the license file for details.

## Security

- Never commit `config.php` or `.env` files to version control
- Keep your xAI API key secure
- Regularly update dependencies
- Use HTTPS in production
