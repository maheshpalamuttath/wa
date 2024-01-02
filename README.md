# Koha Library System - WhatsApp Number Update Feature

## Introduction

This repository contains a PHP script (`add_whatsapp.php`) designed to be integrated into the Koha Library System. The script enables users to update their WhatsApp numbers associated with their library card accounts. This feature proves beneficial for libraries aiming to communicate with users via WhatsApp.

## Prerequisites

Before using this script, ensure that you have a Koha Library System installed and configured on your server. Additionally, make sure the following components are installed:

- PHP
- Apache2 with mod_php enabled
- MySQL

## Installation Steps

### 1. Update System Packages and Install PHP Dependencies:

```bash
sudo apt update
sudo apt install php libapache2-mod-php php-mysql php-cli php-gd php-curl php-mbstring
```

### 2. Navigate to Koha OPAC Directory:

```bash
cd /usr/share/koha/opac/htdocs
```

### 3. Create and Edit `add_whatsapp.php`:

```bash
sudo vim add_whatsapp.php
```

Copy and paste the provided PHP code into the file.

```bash
<?php
// Replace these credentials with your MySQL server details
$servername = "localhost";
$username = "koha_library";
$password = "koha123";
$dbname = "koha_library";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the WhatsApp number and Card number from the form
    $whatsappNumber = $_POST["whatsapp_number"];
    $cardNumber = $_POST["card_number"];

    // Sanitize the input to prevent SQL injection (you may also use other sanitization methods)
    $whatsappNumber = filter_var($whatsappNumber, FILTER_SANITIZE_STRING);
    $cardNumber = filter_var($cardNumber, FILTER_SANITIZE_STRING);

    // Check if the WhatsApp number is a valid 10-digit number
    if (!preg_match('/^\d{10}$/', $whatsappNumber)) {
        echo "Error: Please enter a valid 10-digit WhatsApp number. Redirecting to the index page in 4 seconds...";
        // Redirect to the index.html page after 4 seconds
        echo '<meta http-equiv="refresh" content="4;url=index.html">';
        exit;
    }

    // Add "91" to the beginning of the WhatsApp number
    $whatsappNumber = "91" . $whatsappNumber;

    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to check if the card number exists in the "borrowers" table
    $sql = "SELECT phone, surname FROM borrowers WHERE cardnumber = '$cardNumber'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Card number found, so update the "phone" column
        $row = $result->fetch_assoc();
        $existingPhone = $row["phone"];
        $surname = $row["surname"];

        // Check if the WhatsApp number is different from the one in the "phone" column
        if ($existingPhone != $whatsappNumber) {
            // Update the "phone" column with the new value
            $updateSql = "UPDATE borrowers SET phone = '$whatsappNumber' WHERE cardnumber = '$cardNumber'";

            if ($conn->query($updateSql) === TRUE) {
                echo "WhatsApp number <strong>$whatsappNumber</strong> updated successfully for the user <strong>$surname</strong> (<strong>$cardNumber</strong>). Redirecting to the index page in 4 seconds...";
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } else {
            // WhatsApp number is the same as the one already in the "phone" column
            echo "WhatsApp number <strong>$whatsappNumber</strong> is already linked to card number <strong>$cardNumber</strong> for the user <strong>$surname</strong>. No change. Redirecting to the index page in 4 seconds...";
        }
        // Redirect to the index.html page after 4 seconds
        echo '<meta http-equiv="refresh" content="4;url=index.html">';
    } else {
        echo "Card number <strong>$cardNumber</strong> not found in the borrowers table. Redirecting to the index page in 4 seconds...";
        // Redirect to the index.html page after 4 seconds
        echo '<meta http-equiv="refresh" content="4;url=index.html">';
    }

    // Close the database connection
    $conn->close();
}
?>

```

### 4. Set File Permissions:

```bash
sudo chmod 755 -R add_whatsapp.php
sudo chown www-data:www-data -R add_whatsapp.php
```

### 5. Enable Apache Rewrite Module:

```bash
sudo a2enmod rewrite
sudo systemctl reload apache2 && sudo systemctl restart apache2
```

### 6. Add HTML Customization in Koha:

Navigate to **Tools >> HTML Customizations >> OpacNavRight**. Insert the provided HTML code and save.

#### HTML Customization

```html
<div class="container">
    <img src="https://i.postimg.cc/CLCt4GgF/Whats-App-svg.png" alt="WhatsApp Logo" style="width: 70px; height: 70px; display: block; margin: 0 auto;" />
    <h3 class="text-center">HEARTIAN LIB-CONNECT</h3>
    <p class="text-center mt-3" style="font-size: 14px; color: #888;">Stay Connected, Stay Informed</p>
    <form method="post" action="/update_phone.php" class="mx-auto mt-3">
        <div class="form-group">
            <label for="card_number">Card Number:</label>
            <input type="text" id="card_number" name="card_number" required="" class="form-control" />
        </div>
        <div class="form-group">
            <label for="whatsapp_number">WhatsApp Number:</label>
            <input type="text" id="whatsapp_number" name="whatsapp_number" required="" class="form-control" />
        </div>
        <input type="submit" value="Subscribe" class="btn btn-primary btn-block" />
    </form>
    <p class="text-center mt-4" style="color: #888;">Heartian Lib-Connect, the official <strong>WhatsApp</strong> for library alerts</p>
</div>
```

This setup assumes that the Koha system is running on Apache and uses a specific directory structure. Modify paths and configurations as needed for your environment.

Feel free to adapt and enhance this script based on your specific requirements.
