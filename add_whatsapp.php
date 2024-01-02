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
