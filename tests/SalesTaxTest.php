<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * SalesTaxTest.php
 *
 * Sales Tax PHPUnit Tests
 *
 * PHP version 5.3+
 * PHPUnit 3.6+
 *
 * @author    Daniel Lee <daniel.james.lee@gmail.com>
 * @date      Oct 18, 2012
*/

require_once '../src/SalesTax.php';

/**
 * SalesTaxTest 
 * 
 * @uses      PHPUnit_Framework_TestCase
 * @author    Daniel Lee <daniel.james.lee@gmail.com> 
 */
class SalesTaxTest extends PHPUnit_Framework_TestCase 
{
  /**
   * Tests for invalid columns 
   * 
   * @access public
   * @return void
   */
  public function testInvalidColumns()
  {
    $this->setExpectedException('SalesTaxException'); 
   
    $input = "Foo, Bar
1, Book, 14.22";

    $salesTax = new SalesTax;
    $salesTax->forInput($input)
             ->asCSV();
  }

  /**
   * Tests for missing data 
   * 
   * @access public
   * @return void
   */
  public function testMissingColumns()
  {
    $this->setExpectedException('SalesTaxException'); 
   
    $input = "Quantity, Product, Price
1, 14.22";

    $salesTax = new SalesTax;
    $salesTax->forInput($input)
             ->asCSV();
  }

  /**
   * The original input #1 example had a missing comma. It should throw an exception. 
   * 
   * @access public
   * @return void
   */
  public function testInput1Original() 
  {
    $input = "Quantity, Product, Price
1, book, 12.49
1, music cd, 14.99
1 chocolate bar, 0.85";

    $this->setExpectedException('SalesTaxException');

    $salesTax = new SalesTax;
    $output = $salesTax->forInput($input)
                       ->asCSV();
  }

  /**
   * Modified Input example #1 
   * 
   * @access public
   * @return void
   */
  public function testInput1() 
  {
    $input = "Quantity, Product, Price
1, book, 12.49
1, music cd, 14.99
1, chocolate bar, 0.85";

    // Error in output on word doc. CD needs to be lowercase. Assumed typo.
    $expectedOutput = "1, book, 12.49
1, music cd, 16.49
1, chocolate bar, 0.85

Sales Taxes: 1.50
Total: 29.83";

    $salesTax = new SalesTax;
    $output = $salesTax->forInput($input)
                       ->asCSV();

    $this->assertEquals($output, $expectedOutput);
  }

  /**
   * Input example #2. 
   * 
   * @access public
   * @return void
   */
  public function testInput2() 
  {
    $input = "Quantity, Product, Price
1, imported box of chocolates, 10.00
1, imported bottle of perfume, 47.50";

    $expectedOutput = "1, imported box of chocolates, 10.50
1, imported bottle of perfume, 54.65

Sales Taxes: 7.65
Total: 65.15";

    $salesTax = new SalesTax;
    $output = $salesTax->forInput($input)
                       ->asCSV();
 
    $this->assertEquals($output, $expectedOutput);
  }

  /**
   * Input example #3. I assume there was a typo with 'box of imported chocolates'
   * to 'imported box of chocolates'.
   * 
   * @access public
   * @return void
   */
  public function testInput3() 
  {
    $input = "Quantity, Product, Price
1, imported bottle of perfume, 27.99
1, bottle of perfume, 18.99
1, packet of headache pills, 9.75
1, box of imported chocolates, 11.25";

    $expectedOutput = "1, imported bottle of perfume, 32.19
1, bottle of perfume, 20.89
1, packet of headache pills, 9.75
1, box of imported chocolates, 11.85

Sales Taxes: 6.70
Total: 74.68";

    $salesTax = new SalesTax;
    $output = $salesTax->forInput($input)
                       ->asCSV();
 
    $this->assertEquals($output, $expectedOutput);
  }
  
  /**
   * Tests multiple items. Figured we may as well test this 
   * 
   * @access public
   * @return void
   */
  public function testMultipleQuantities() 
  {
    $input = "Quantity, Product, Price
2, imported bottle of perfume, 27.99
1, bottle of perfume, 18.99
3, packet of headache pills, 9.75
1, box of imported chocolates, 11.25";

    $expectedOutput = "2, imported bottle of perfume, 32.19
1, bottle of perfume, 20.89
3, packet of headache pills, 9.75
1, box of imported chocolates, 11.85

Sales Taxes: 10.90
Total: 126.37";

    $salesTax = new SalesTax;
    $output = $salesTax->forInput($input)
                       ->asCSV();
 
    $this->assertEquals($output, $expectedOutput);
  }

  /**
   * Tests some big numbers 
   * 
   * @access public
   * @return void
   */
  public function testLargeNumbers() 
  {
    $input = "Quantity, Product, Price
10, chewing gum, 1337
3, vinyls, 329
400, pills, 9.99
200, imported books, 19.99";

    $expectedOutput = "10, chewing gum, 1,470.70
3, vinyls, 361.90
400, pills, 9.99
200, imported books, 20.99

Sales Taxes: 1,635.70
Total: 23,986.70";

    $salesTax = new SalesTax;
    $output = $salesTax->forInput($input)
                       ->asCSV();
 
    $this->assertEquals($output, $expectedOutput);
  }

  /**
   * Modified switched columns 
   * 
   * @access public
   * @return void
   */
  public function testSwitchedColumns1() 
  {
    $input = "Quantity, Price, Product
1, 12.49, book
1, 14.99, music cd
1, 0.85, chocolate bar";

    // Error in output on word doc. CD needs to be lowercase. Assumed typo.
    $expectedOutput = "1, book, 12.49
1, music cd, 16.49
1, chocolate bar, 0.85

Sales Taxes: 1.50
Total: 29.83";

    $salesTax = new SalesTax;
    $output = $salesTax->forInput($input)
                       ->asCSV();

    $this->assertEquals($output, $expectedOutput);
  }


}
