# Certificate Verifier WordPress Plugin

A WordPress plugin that allows organizations to manage and verify certificates. Users can add certificates individually or bulk import via CSV, while visitors can verify certificates using a simple verification form.

## Features

- Add individual certificates with details:
  - Name
  - Email
  - Certificate Number
  - Issue Date

- Bulk import certificates via CSV
- Export all certificates to CSV
- Edit existing certificates
- Delete certificates
- Public certificate verification via shortcode
- Responsive, modern UI
- Secure data handling with input sanitization

## Installation

1. Download the plugin files
2. Zip `certificate-verifier` directory
3. On the WordPress admin, add new plugin and upload the compressed plugin
3. Activate the plugin  
## Usage

### Admin Interface

Access the admin interface by clicking "Certificate Verifier" in the WordPress admin menu. Here you can:

1. **Add New Certificate**
   - Fill in the required fields (Name, Email, Certificate Number, Issue Date)
   - Click "Add Certificate"

2. **Import Certificates via CSV**
   - Prepare a CSV file with the following columns:
     ```
     Name,Email,Certificate Number,Issue Date
     John Doe,john@example.com,CERT-001,2024-01-01
     ```
   - Click "Choose File" in the Upload CSV section
   - Select your CSV file
   - Click "Upload CSV"

3. **Export Certificates**
   - Click "Export CSV" to download all certificates

4. **Manage Existing Certificates**
   - View all certificates in the table
   - Click "Edit" to modify a certificate
   - Click "Update" to save changes
   - Click "Delete" to remove a certificate

### Public Certificate Verification

Add the verification form to any page or post using the shortcode:

```
[certificate_verifier]
```

This will display a form where users can enter a certificate number to verify its authenticity.

## CSV Format

When importing certificates via CSV:

1. The first row should contain column headers
2. Required columns:
   - Name
   - Email
   - Certificate Number
   - Issue Date (YYYY-MM-DD format)
3. Example:
   ```
   Name,Email,Certificate Number,Issue Date
   John Doe,john@example.com,CERT-001,2024-01-01
   Jane Smith,jane@example.com,CERT-002,2024-01-02
   ```

## Security Features

- Input sanitization for all form submissions
- Data validation before database operations
- Unique certificate numbers enforced
- WordPress nonce verification for forms
- Error logging for troubleshooting

## Screen shots

![image](https://github.com/user-attachments/assets/01920bda-5366-4de1-9714-f9f8782188de)

![image](https://github.com/user-attachments/assets/d004abfb-cc24-4e1b-b4b4-68c1278b5215)

Link: [https://verify.cair-nepal.org/](https://verify.cair-nepal.org/)

## Support

For issues, questions, or suggestions, please create an issue in the repository or contact the plugin author.

## License

This plugin is licensed under the GPL v2 or later.

## Author

Tek Raj Chhetri

## Version

2.7
