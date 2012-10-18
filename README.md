FD Test
=========
# About

Original brief available in BRIEF.md

The tests for each class can be found in the tests/ directory.

# Requirements

* PHP 5.3+ 
* PHPUnit 3.6+ 

# Examples

## Input #1
```php
require_once 'src/SalesTax.php';

$input = "Quantity, Product, Price
1, book, 12.49
1, music cd, 14.99
1, chocolate bar, 0.85";
    
$salesTax = new SalesTax;
echo $salesTax->forInput($input)
              ->asCSV();
```
## Input #2
```php
require_once 'src/SalesTax.php';

$input = "Quantity, Product, Price
1, imported box of chocolates, 10.00
1, imported bottle of perfume, 47.50";
    
$salesTax = new SalesTax;
echo $salesTax->forInput($input)
              ->asCSV();
```