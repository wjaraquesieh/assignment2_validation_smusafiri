<?php
/*******w******** 
    
    Name: Samuel Musafiri
    Date: 2024-05-27
    Description: Form Validation

****************/


// These are the items
$items = [
    ['position' => 1, 'name' => 'MacBook', 'price' => 1899.99, 'quantity' => 0],
    ['position' => 2, 'name' => 'Razer Gaming Mouse', 'price' => 79.99, 'quantity' => 0],
    ['position' => 3, 'name' => 'Portable Hard Drive', 'price' => 179.99, 'quantity' => 0],
    ['position' => 4, 'name' => 'Google Nexus 7', 'price' => 249.99, 'quantity' => 0],
    ['position' => 5, 'name' => 'Footpedal', 'price' => 119.99, 'quantity' => 0]
];


// Accessing Data using POST method, if not null
$email = isset($_POST['email']) ? $_POST['email'] : null;
$postalCode = isset($_POST['postal']) ? $_POST['postal'] : null;
$fullName = isset($_POST['fullname']) ? $_POST['fullname'] : null;
$address = isset($_POST['address']) ? $_POST['address'] : null;
$city = isset($_POST['city']) ? $_POST['city'] : null;
$province = isset($_POST['province']) ? $_POST['province'] : null;

// Total Order Amount Initilized
$totalOrderAmount = 0;

// Updating the Quantities,itval converts quantity to interger value, if not set, it is 0
foreach ($items as $key => $item) {
    $itemQty = isset($_POST['qty' . $item['position']]) ? intval($_POST['qty' . $item['position']]) : 0;
    $items[$key]['quantity'] = $itemQty;
}


// Array for the errors
$errors = array();

// Checking if Items in cart
function itemsExist() {
    global $items;
    $totalQuantity = 0;

    foreach($items as $key => $item) {
    $totalQuantity += $item['quantity'];
    }

    if($totalQuantity > 0) {
    return true;
    } else {
    return false;
    }
}

// Getting the total amount of an item
function getItemTotal($item) {
    $itemTotal = $item['price'] * $item['quantity'];

    return $itemTotal;
}

// validate the POST data
function validateData() {
    global $errors;
    global $items;

    // Checking if email exists
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $errors[] = 'Email is not valid or not provided!'; 
     }

      // Validate credit card number
    $creditCardNumber = filter_input(INPUT_POST, 'cardnumber', FILTER_VALIDATE_INT);
    if (!$creditCardNumber || strlen((string)$creditCardNumber) !== 10) {
        $errors[] = 'Credit card number is invalid or missing!';
    }

    // Validate postal code
    $postalCode = filter_input(INPUT_POST, 'postal');
    if (!preg_match('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/', $postalCode)) {
        $errors[] = 'Postal code is invalid or missing!';
    }

    // Validate credit card year
    $currentYear = date('Y');
    $creditCardYear = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, ['options' => ['min_range' => $currentYear, 'max_range' => $currentYear + 5]]);
    if (!$creditCardYear) {
        $errors[] = 'Credit card expiration year is invalid or missing!';
    }


    // Validate credit card month
    $currentMonth = date('n'); 
    $creditCardMonth = ($currentYear == $_POST['year']) ? filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT, ['options' => ['min_range' => $currentMonth, 'max_range' => 12]]) : filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
    if (!$creditCardMonth) {
        $errors[] = 'Credit card expiration month is invalid or missing!';
    }

    // Checking credit type
    $creditCardType = isset($_POST['cardtype']);
    if (!$creditCardType) {
        $errors[] = 'Credit card type is not selected!';
    }

    // Checking required fields
    $fieldsToCheck = ['fullname', 'cardname', 'address', 'city', 'province'];
    foreach ($fieldsToCheck as $field) {

        $value = trim(filter_input(INPUT_POST, $field));

        if (empty($value)) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is empty or missing!';
        }
    }

    // Validating quantities
    foreach ($items as $key => $item) {
        $qty = $item['quantity'];

        if(is_numeric($qty) && $qty >= 0) continue;

        $errors[] = 'Quantity for item ' . ($key + 1) . " (" . $item['name'] . ")" . ' is not an integer!';
    }

    if(!empty($errors)) {
    return false;
    } else {
    return true;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="thankyou.css">
    <title>Thanks for your order!</title>
</head>
<body>
<?php
$isDataValid = validateData();

$displayError = false;

$errorMessages = [];

$invoiceDetails = '';

$displayInvoice = false;

if (!$isDataValid) {
    $displayError = true;
    foreach ($errors as $error) {
        $errorMessages[] = htmlspecialchars($error);
    }
} else {
    if (itemsExist()) {
        $invoiceDetails = "Thanks for your order " . htmlspecialchars($fullName) . ".";
        $displayInvoice = true;
    } else {
        $invoiceDetails = "Your cart is empty.";
        $displayInvoice = true;
    }
}
?>

<?php if ($displayError): ?>
        <p>The form could not be processed due to the following errors:</p>
        <ul>
            <?php foreach ($errorMessages as $errorMessage): ?>
                <li><?= $errorMessage ?></li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($displayInvoice): ?>
    <div class="invoice">
        <h2><?= $invoiceDetails ?></h2>

    <?php if (itemsExist()): ?>
    <h3>Here is the summary of your order:</h3>
    <table>
    <colgroup>
            <col style="width: 15%">
            <col style="width: 40%">
            <col style="width: 25%">
            <col style="width: 20%">
        </colgroup>
        <tr>
            <th class="alignleft" colspan="4">Address Information</th>
        </tr>
        <tr>
            <td class="align bold">Address:</td>
            <td><?= $address ?></td>
            <td class="align bold">City:</td>
            <td><?= $city ?></td>
        </tr>
        <tr>
            <td class="align bold">Province:</td>
            <td ><?= $province ?></td>
            <td class="align bold">Postal Code:</td>
            <td><?= $postalCode ?></td>
        </tr>
        <tr>
            <td colspan="3" class="align bold">Email:</td>
            <td colspan="2"><?= $email ?></td>
        </tr>
    </table>

    <table>
        <colgroup>
            <col style="width: 20%">
            <col style="width: 60%">
            <col style="width: 20%">
        </colgroup>
        <tr>
            <th class="alignleft" colspan="3">Order Information</th>
        </tr>
        <tr>
            <td class="bold">Quantity</td>
            <td class="bold">Description</td>
            <td class="bold">Cost</td>
        </tr>
        <?php foreach ($items as $item): ?>
            <?php if ($item['quantity'] > 0): ?>
             <tr>
              <td><?= $item['quantity'] ?></td>
              <td><?= $item['name'] ?></td>
              <?php $totalOrderAmount += getItemTotal($item) ?>
              <td class="alignright"><?= getItemTotal($item) ?></td>
             </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        <tr>
            <td class="alignright bold" colspan="2">Totals</td>
            <td class="alignright bold">$ <?= $totalOrderAmount ?></td>
        </tr>
    </table>
    <?php endif; ?>
    </div>
  
    <?php endif; ?>

</body>
</html>

