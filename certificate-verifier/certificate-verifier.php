<?php
/**
 * Plugin Name: Certificate Verifier
 * Description: Allows users to add certificate details via form or CSV, view, edit, verify, and export all certificate details in CSV.
 * Version: 3.0
 * Author: Tek Raj Chhetri
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Function to log errors
function cv_log_error($message) {
    error_log('Certificate Verifier: ' . $message);
}

// Create or update database table on activation with error handling
function cv_create_or_update_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id MEDIUMINT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        certificate_number VARCHAR(100) NOT NULL UNIQUE,
        issued_date DATE NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    try {
        dbDelta($sql);
        update_option('cv_plugin_version', '2.7');
    } catch (Exception $e) {
        cv_log_error('Database creation failed: ' . $e->getMessage());
    }
}
register_activation_hook(__FILE__, 'cv_create_or_update_table');

// Export all certificates to CSV
function cv_export_csv() {
    if (isset($_POST['cv_export_csv'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'certificates';
        $certificates = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        if (!empty($certificates)) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=certificates.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Name', 'Email', 'Certificate Number', 'Issued Date']);
            foreach ($certificates as $certificate) {
                fputcsv($output, $certificate);
            }
            fclose($output);
            exit;
        } else {
            echo '<div class="error notice"><p>No certificates available for export.</p></div>';
        }
    }
}
add_action('admin_init', 'cv_export_csv');

// Handle form submissions
function cv_handle_form_submissions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';

    // Handle new certificate submission
    if (isset($_POST['cv_submit'])) {
        $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($_POST['cv_name']),
                'email' => sanitize_email($_POST['cv_email']),
                'certificate_number' => sanitize_text_field($_POST['cv_certificate_number']),
                'issued_date' => sanitize_text_field($_POST['cv_issued_date'])
            ),
            array('%s', '%s', '%s', '%s')
        );
        if ($wpdb->last_error) {
            echo '<div class="error notice"><p>Error adding certificate: ' . $wpdb->last_error . '</p></div>';
        } else {
            echo '<div class="updated notice"><p>Certificate added successfully!</p></div>';
        }
    }

    // Handle CSV upload
    if (isset($_POST['cv_upload_csv'])) {
        if (!empty($_FILES['cv_csv_file']['tmp_name'])) {
            $file = fopen($_FILES['cv_csv_file']['tmp_name'], 'r');
            // Skip header row
            fgetcsv($file);
            while (($data = fgetcsv($file)) !== FALSE) {
                if (count($data) >= 4) {
                    $wpdb->insert(
                        $table_name,
                        array(
                            'name' => sanitize_text_field($data[0]),
                            'email' => sanitize_email($data[1]),
                            'certificate_number' => sanitize_text_field($data[2]),
                            'issued_date' => sanitize_text_field($data[3])
                        ),
                        array('%s', '%s', '%s', '%s')
                    );
                }
            }
            fclose($file);
            echo '<div class="updated notice"><p>CSV data imported successfully!</p></div>';
        }
    }

    // Handle update
    if (isset($_POST['cv_update'])) {
        $wpdb->update(
            $table_name,
            array(
                'name' => sanitize_text_field($_POST['cv_name']),
                'email' => sanitize_email($_POST['cv_email']),
                'certificate_number' => sanitize_text_field($_POST['cv_certificate_number']),
                'issued_date' => sanitize_text_field($_POST['cv_issued_date'])
            ),
            array('id' => $_POST['cv_id']),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
        echo '<div class="updated notice"><p>Certificate updated successfully!</p></div>';
    }

    // Handle delete
    if (isset($_POST['cv_delete'])) {
        $wpdb->delete(
            $table_name,
            array('id' => $_POST['cv_id']),
            array('%d')
        );
        echo '<div class="updated notice"><p>Certificate deleted successfully!</p></div>';
    }
}
add_action('admin_init', 'cv_handle_form_submissions');

// Admin menu
function cv_admin_menu() {
    add_menu_page('Certificate Verifier', 'Certificate Verifier', 'manage_options', 'certificate-verifier', 'cv_admin_page', 'dashicons-awards', 6);
}
add_action('admin_menu', 'cv_admin_menu');

// Ensure admin page with forms is not removed
// Add custom styles
function cv_add_custom_styles() {
    ?>
    <style>
        .cv-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }
        .cv-form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .cv-form input[type="text"],
        .cv-form input[type="email"],
        .cv-form input[type="date"],
        .cv-form input[type="file"] {
            width: 100%;
            padding: 8px 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .cv-form input[type="file"] {
            padding: 4px 8px;
        }
        .cv-form .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        .cv-form .form-group {
            flex: 1;
            min-width: 200px;
        }
        .cv-btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            padding: 8px 16px;
            font-size: 14px;
            line-height: 1.5;
            border-radius: 4px;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
            margin: 5px;
        }
        .cv-btn-primary {
            color: #fff;
            background-color: #0073aa;
            border: 1px solid #0073aa;
        }
        .cv-btn-primary:hover {
            background-color: #006291;
            border-color: #006291;
        }
        .cv-btn-secondary {
            color: #333;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .cv-btn-secondary:hover {
            background-color: #e2e6ea;
            border-color: #ddd;
        }
        .cv-btn-danger {
            color: #fff;
            background-color: #dc3545;
            border: 1px solid #dc3545;
        }
        .cv-btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .cv-table {
            width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
            border-collapse: collapse;
        }
        .cv-table th,
        .cv-table td {
            padding: 12px;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        .cv-table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .cv-table tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.05);
        }
        .cv-section {
            margin-bottom: 30px;
        }
        .cv-section h2 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        @media (max-width: 768px) {
            .cv-form .form-group {
                flex: 100%;
            }
            .cv-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <?php
}
add_action('admin_head', 'cv_add_custom_styles');

function cv_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';
    $certificates = $wpdb->get_results("SELECT * FROM $table_name");
    
    echo '<div class="wrap cv-container">';
    echo '<h1>Certificate Verifier</h1>';
    
    echo '<div class="cv-section">';
    echo '<h2>Add New Certificate</h2>';
    echo '<form method="post" class="cv-form">';
    echo '<div class="form-row">';
    echo '<div class="form-group"><input type="text" name="cv_name" placeholder="Name" required></div>';
    echo '<div class="form-group"><input type="email" name="cv_email" placeholder="Email" required></div>';
    echo '<div class="form-group"><input type="text" name="cv_certificate_number" placeholder="Certificate Number" required></div>';
    echo '<div class="form-group"><input type="date" name="cv_issued_date" required></div>';
    echo '</div>';
    echo '<input type="submit" name="cv_submit" value="Add Certificate" class="cv-btn cv-btn-primary">';
    echo '</form>';
    echo '</div>';
    
    echo '<div class="cv-section">';
    echo '<h2>Upload CSV</h2>';
    echo '<form method="post" enctype="multipart/form-data" class="cv-form">';
    echo '<div class="form-row">';
    echo '<div class="form-group"><input type="file" name="cv_csv_file" accept=".csv" required></div>';
    echo '</div>';
    echo '<input type="submit" name="cv_upload_csv" value="Upload CSV" class="cv-btn cv-btn-secondary">';
    echo '</form>';
    echo '</div>';
    
    echo '<div class="cv-section">';
    echo '<h2>Export All Certificates</h2>';
    echo '<form method="post" class="cv-form">';
    echo '<input type="submit" name="cv_export_csv" value="Export CSV" class="cv-btn cv-btn-secondary">';
    echo '</form>';
    echo '</div>';
    
    echo '<div class="cv-section">';
    echo '<h2>Existing Certificates</h2>';
    echo '<table class="cv-table">';
    echo '<tr><th>Name</th><th>Email</th><th>Certificate Number</th><th>Issued Date</th><th>Actions</th></tr>';
    foreach ($certificates as $certificate) {
        echo "<tr id='certificate-{$certificate->id}'>
            <form method='post'>
            <td><input type='text' name='cv_name' value='{$certificate->name}' required readonly></td>
            <td><input type='email' name='cv_email' value='{$certificate->email}' required readonly></td>
            <td><input type='text' name='cv_certificate_number' value='{$certificate->certificate_number}' required readonly></td>
            <td><input type='date' name='cv_issued_date' value='{$certificate->issued_date}' required readonly></td>
            <td>
                <input type='hidden' name='cv_id' value='{$certificate->id}'>
                <button type='button' onclick='toggleEdit({$certificate->id})' class='cv-btn cv-btn-secondary edit-btn'>Edit</button>
                <input type='submit' name='cv_update' value='Update' class='cv-btn cv-btn-primary update-btn' style='display:none;'>
                <input type='submit' name='cv_delete' value='Delete' class='cv-btn cv-btn-danger'>
            </td>
            </form>
        </tr>";
    }
    echo '</table>';
    echo '</div>';
    
    // Add JavaScript for edit functionality
    ?>
    <script type="text/javascript">
    function toggleEdit(id) {
        const row = document.getElementById('certificate-' + id);
        const inputs = row.getElementsByTagName('input');
        const editBtn = row.querySelector('.edit-btn');
        const updateBtn = row.querySelector('.update-btn');
        
        for (let i = 0; i < inputs.length; i++) {
            if (inputs[i].type !== 'hidden' && inputs[i].type !== 'submit') {
                if (inputs[i].readOnly) {
                    inputs[i].readOnly = false;
                    editBtn.style.display = 'none';
                    updateBtn.style.display = 'inline-block';
                } else {
                    inputs[i].readOnly = true;
                    editBtn.style.display = 'inline-block';
                    updateBtn.style.display = 'none';
                }
            }
        }
    }
    </script>
    <?php
}

// Add shortcode for verification
// Add frontend styles
function cv_add_frontend_styles() {
    if (has_shortcode(get_post()->post_content, 'certificate_verifier')) {
        ?>
        <style>
            .cv-verify-container {
                max-width: 600px;
                margin: 40px auto;
                padding: 0 15px;
            }
            .cv-verify-form {
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .cv-verify-form label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #333;
            }
            .cv-verify-form input[type="text"] {
                width: 100%;
                padding: 12px;
                margin-bottom: 20px;
                border: 2px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                transition: border-color 0.3s ease;
            }
            .cv-verify-form input[type="text"]:focus {
                border-color: #0073aa;
                outline: none;
            }
            .cv-verify-btn {
                background: #0073aa;
                color: #fff;
                padding: 12px 24px;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s ease;
            }
            .cv-verify-btn:hover {
                background: #005177;
            }
            .cv-result {
                margin-top: 30px;
                padding: 20px;
                border-radius: 4px;
                background: #f8f9fa;
                border-left: 4px solid #0073aa;
            }
            .cv-result.success {
                background: #f0f9eb;
                border-left-color: #67c23a;
            }
            .cv-result.error {
                background: #fef0f0;
                border-left-color: #f56c6c;
            }
            .cv-result h3 {
                margin: 0 0 15px 0;
                color: #333;
            }
            .cv-result p {
                margin: 8px 0;
                color: #666;
            }
            .cv-result strong {
                color: #333;
                margin-right: 5px;
            }
            @media (max-width: 768px) {
                .cv-verify-container {
                    margin: 20px auto;
                }
                .cv-verify-form {
                    padding: 20px;
                }
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'cv_add_frontend_styles');

function cv_verification_form() {
    ob_start();
    echo '<div class="cv-verify-container">';
    echo '<div class="cv-verify-form">';
    echo '<form method="post">';
    echo '<label for="cv_verify_certificate">Certificate Number</label>';
    echo '<input type="text" id="cv_verify_certificate" name="cv_verify_certificate" placeholder="Enter your certificate number" required>';
    echo '<button type="submit" name="cv_verify_submit" class="cv-verify-btn">Verify Certificate</button>';
    echo '</form>';
    
    if (isset($_POST['cv_verify_submit'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'certificates';
        $cert_number = sanitize_text_field($_POST['cv_verify_certificate']);
        $certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE certificate_number = %s", $cert_number));

        if ($certificate) {
            echo "<div class='cv-result success'>";
            echo "<h3>Certificate Verified ✓</h3>";
            echo "<p><strong>Name:</strong> " . esc_html($certificate->name) . "</p>";
            echo "<p><strong>Certificate Number:</strong> " . esc_html($certificate->certificate_number) . "</p>";
            echo "<p><strong>Issue Date:</strong> " . esc_html($certificate->issued_date) . "</p>";
            echo "</div>";
        } else {
            echo "<div class='cv-result error'>";
            echo "<h3>Certificate Not Found ✗</h3>";
            echo "<p>The certificate number you entered could not be found in our records. Please check the number and try again.</p>";
            echo "</div>";
        }
    }
    echo '</div>';
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('certificate_verifier', 'cv_verification_form');

cv_log_error('Plugin loaded successfully.');

?>
