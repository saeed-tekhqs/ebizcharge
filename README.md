# EBizCharge

EBizCharge allows the processing of credit card payments via the EBizCharge Gateway. You must generate a sourcekey in your merchant console.

# To enable the EBizCharge module:
1.	Set “Enabled” to Yes.
2.	Paste your Security Id into the “Security Id” field (This key must be created in your EBizCharge Merchant Console).
3.	Enter your User Id in the “User Id” field. This must match the User Id that was entered when the Security Id was created. If you do not have one, leave this field blank.
4.	Enter your Password in “Password” field. This must match the User Id that was entered when the Security Id was created. If you do not have one, leave this field blank.
5.	Change “Accepted Currency” to US Dollar.
6.	Click on the “Save Config” button in the top right corner.

You should now be able to process credit cards via your EBizCharge account.

# Configuration Options

# Payment Action
Select “Authorize and Capture” to make an immediate charge. Select “Authorize Only” to authorize the card and then manually settle the funds at a later time (such as during shipping).
# Title
Select a title for this Payment Option, such as “Secure Credit Card” or “EBizCharge”.
# Security Id
Please enter your EBizCharge Security Id generated from your EBizCharge Merchant Console.
# User ID
Enter a user ID, if available. 
# Password
Enter Password, if available. 
# Transaction Description
Enter a description that will appear on all transactions, or use #[orderid] to display the order ID.
# Accepted Currency
Unless you have a Multi-Currency gateway account, this is always US Dollars.
# New Order Status
Select the status that will appear on all new orders.
# Sort Order
If you have multiple payment options, enter 1 to have this payment module be first.
# Payment from Applicable Countries
If you accept payments from all countries, select this option, or select “Specific” and the next section will be active.
# Payment from Specific Countries
Select specific countries to accept payment from.
# Minimum Order Total
Enter the minimum dollar amount for an order.
# Maximum Order Total
Enter the maximum dollar amount for an order.
# Automatically Save Payment Methods
Click yes to Automatically Save Payment Methods during checkout.
# EConnect Functionality
# Item Source
Click default items source.
# Enable Upload Options
Click yes to enable upload functionality to EConnect.

Upload Customers

Upload Items

Upload Orders

Upload Invoices

# Enable Download Options
Click yes to enable download functionality to EConnect.
# Choose Shipping Method 
Select default shipping method for download orders from EConnect.

Download Customers

Download Orders

Download Items
