# Receive an email when the product is back in stock


## Inventory configuration
When the stock of a product changes from zero or below zero to any positive value, the task of sending an email to the customer is added to OroCommerce message queue.

![BO Inventory](media/synolia_stock_alert_bo_inventory.png)

## Receiving the email
An email is sent to the customer if the customer subscribed to that product and the inventory level is now positive.

![Email Sent](media/synolia_stock_alert_email.png)
