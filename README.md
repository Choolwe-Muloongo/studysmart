# Document AI Processor

A PHP application that uses OpenRouter AI to process documents and generate summaries, questions, flashcards, and quizzes.

## Features

- 📄 **Document Upload**: Support for TXT, PDF, DOC, and DOCX files
- 🤖 **AI Processing**: Uses OpenRouter API with Claude 3.5 Sonnet
- 📝 **Summary Generation**: Comprehensive document summaries
- ❓ **Question Generation**: Thoughtful questions to test understanding
- 🗂️ **Flashcard Creation**: Key concepts with Q&A format
- 📋 **Quiz Generation**: Multiple choice questions with answers

## Setup Instructions

### 1. Prerequisites

- PHP 7.4 or higher
- cURL extension enabled
- Web server (Apache, Nginx, or PHP built-in server)
- OpenRouter API key

### 2. Installation

1. **Clone or download the files** to your web server directory
2. **Get an OpenRouter API key**:
   - Visit [OpenRouter](https://openrouter.ai/)
   - Sign up for an account
   - Generate an API key
3. **Configure the API key**:
   - Open `config.php`
   - Replace `'your_openrouter_api_key_here'` with your actual API key
4. **Set up file permissions**:
   ```bash
   chmod 755 uploads/
   ```

### 3. Running the Application

#### Option A: Using PHP Built-in Server
```bash
php -S localhost:8000
```
Then visit `http://localhost:8000/document_processor.php`

#### Option B: Using Apache/Nginx
Place the files in your web server directory and access via your domain.

### 4. Usage

1. **Upload a Document**: Click "Choose Document" and select a file (TXT, PDF, DOC, or DOCX)
2. **Process**: Click "Process Document" to start AI processing
3. **View Results**: The application will generate:
   - **Summary**: Key points and main concepts
   - **Questions**: 10 questions to test understanding
   - **Flashcards**: 15 Q&A cards for key concepts
   - **Quiz**: 20 multiple choice questions with answers

## File Structure

```
document_processor/
├── document_processor.php    # Main application file
├── config.php               # Configuration settings
├── README.md               # This file
└── uploads/                # Temporary upload directory
```

## Configuration Options

Edit `config.php` to customize:

- **API Settings**: OpenRouter API key and base URL
- **File Limits**: Maximum file size (default: 10MB)
- **AI Model**: Choose different OpenRouter models
- **Token Limits**: Adjust max tokens for responses
- **Debug Mode**: Enable/disable error reporting

## Supported File Types

- **TXT**: Plain text files
- **PDF**: PDF documents (text extraction)
- **DOC**: Microsoft Word documents
- **DOCX**: Microsoft Word documents (newer format)

## API Models Available

The application uses OpenRouter, which provides access to various AI models:

- `anthropic/claude-3.5-sonnet` (default)
- `openai/gpt-4`
- `google/gemini-pro`
- `meta-llama/llama-2-70b-chat`

To change the model, edit the `DEFAULT_MODEL` constant in `config.php`.

## Security Considerations

- **API Key**: Never commit your API key to version control
- **File Uploads**: The application validates file types and sizes
- **Temporary Files**: Uploaded files are automatically deleted after processing
- **Error Handling**: Sensitive information is not exposed in error messages

## Troubleshooting

### Common Issues

1. **"API request failed"**
   - Check your OpenRouter API key in `config.php`
   - Ensure you have sufficient credits in your OpenRouter account
   - Verify internet connectivity

2. **"Could not read file content"**
   - Check file permissions on the uploads directory
   - Ensure the file is not corrupted
   - Verify the file format is supported

3. **"File size must be less than 10MB"**
   - Reduce the file size or increase the limit in `config.php`

4. **"Only TXT, PDF, DOC, and DOCX files are allowed"**
   - Convert your file to a supported format
   - Or add the file type to `ALLOWED_FILE_TYPES` in `config.php`

### Debug Mode

Enable debug mode in `config.php` to see detailed error messages:

```php
define('DEBUG_MODE', true);
```

## Customization

### Adding New AI Features

To add new AI processing features, extend the `DocumentProcessor` class:

```php
public function generateNewFeature($content) {
    $prompt = "Your custom prompt here\n\nDocument content:\n" . $content;
    return $this->callOpenRouterAPI($prompt);
}
```

### Styling

The application uses a modern dark theme with red accents. To customize the styling, edit the CSS in the `<style>` section of `document_processor.php`.

## License

This project is open source and available under the MIT License.

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Verify your OpenRouter API key and account status
3. Ensure all prerequisites are met
4. Check the PHP error logs for detailed error messages 