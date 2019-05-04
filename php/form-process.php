<?php

$errorMSG = "";

// NAME
if (empty($_POST["name"])) {
    $errorMSG = "Name is required ";
} else {
    $name = $_POST["name"];
}

// EMAIL
if (empty($_POST["email"])) {
    $errorMSG .= "Email is required ";
} else {
    $email = $_POST["email"];
}

// MSG SUBJECT
if (empty($_POST["msg_subject"])) {
    $errorMSG .= "Subject is required ";
} else {
    $msg_subject = $_POST["msg_subject"];
}

// MESSAGE
if (empty($_POST["message"])) {
    $errorMSG .= "Message is required ";
} else {
    $message = $_POST["message"];
}

// ATTACHMENT
// Upload attachment file
if(!empty($_FILES["attachment"]["name"])) {

    // File path config
    $targetDir = "uploads/";
    $fileName = basename($_FILES["attachment"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Allow certain file formats
    $allowTypes = array('pdf', 'doc', 'docx', 'jpg', 'png', 'jpeg');
    if (in_array($fileType, $allowTypes)) {
        // Upload file to the server
        if(move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetFilePath)){
            $uploadedFile = $targetFilePath;
        } else {
            $uploadStatus = 0;
            $statusMsg = "Sorry, there was an error uploading your file.";
        }
    } else {
        $uploadStatus = 0;
        $statusMsg = 'Sorry, only PDF, DOC, JPG, JPEG, & PNG files are allowed to upload.';
    }
}

// Recipient
$toEmail = 'contact@light-up.vn';

// Sender
$from = 'contact@light-up.vn';
$fromName = 'Contact mail';

// Subject
$emailSubject = 'Contact Request Submitted by ' . $name;

// Message
$htmlContent = '<h2>Contact Request Submitted</h2>
                    <p><b>Name:</b> ' . $name.'</p>
                    <p><b>Email:</b> ' . $email.'</p>
                    <p><b>Subject:</b> ' . $msg_subject.'</p>
                    <p><b>Message:</b><br/>' . $message.'</p>';

// Header for sender info
$headers = "From: $fromName"." <".$from.">";

if(!empty($uploadedFile) && file_exists($uploadedFile)){
    
    // Boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
    
    // Headers for attachment
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
    
    // Multipart boundary
    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";
    
    // Preparing attachment
    if(is_file($uploadedFile)){
        $message .= "--{$mime_boundary}\n";
        $fp =    @fopen($uploadedFile,"rb");
        $data =  @fread($fp,filesize($uploadedFile));
        @fclose($fp);
        $data = chunk_split(base64_encode($data));
        $message .= "Content-Type: application/octet-stream; name=\"".basename($uploadedFile)."\"\n" .
            "Content-Description: ".basename($uploadedFile)."\n" .
            "Content-Disposition: attachment;\n" . " filename=\"".basename($uploadedFile)."\"; size=".filesize($uploadedFile).";\n" .
            "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
    }
    
    $message .= "--{$mime_boundary}--";
    $returnpath = "-f" . $email;
    
    // Send email
    $mail = mail($toEmail, $emailSubject, $message, $headers, $returnpath);
    
    // Delete attachment file from the server
    @unlink($uploadedFile);
} else {
    // Set content-type header for sending HTML email
    $headers .= "\r\n". "MIME-Version: 1.0";
    $headers .= "\r\n". "Content-type:text/html;charset=UTF-8";
    
    // Send email
    $mail = mail($toEmail, $emailSubject, $htmlContent, $headers);
}

// redirect to success page
if ($mail && $errorMSG == "") {
   $url = "https://light-up.vn/contact_success.html";
   header("Location: $url");
} else {
    if ($errorMSG == "") {
        echo "Something went wrong :(";
    } else {
        echo $errorMSG;
    }
}

?>