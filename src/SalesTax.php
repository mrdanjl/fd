<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * SalesTax.php
 *
 * Calculates the sales tax given an input
 *
 * PHP version 5.3+
 *
 * @author    Daniel Lee <daniel.james.lee@gmail.com>
 * @date      Oct 18, 2012
 */

/**
 * SalesTaxException 
 * 
 * @author    Daniel Lee <daniel.james.lee@gmail.com> 
 */
class SalesTaxException extends \Exception {}

/**
 * SalesTax 
 * 
 * @author    Daniel Lee <daniel.james.lee@gmail.com> 
 */
class SalesTax 
{
    /**
     * Avaialble field and validation
     * 
     * @var string
     * @access private
     */
    private $fields = array('Quantity' => '/^(\d+)$/i',
                            'Product'  => '/^(.*)$/i',
                            'Price'    => '/^\d+(.\d{1,2})?$/');

    /**
     * input 
     * 
     * @var mixed
     * @access private
     */
    private $input;

    /**
     * data 
     * 
     * @var mixed
     * @access private
     */
    private $data;

    /**
     * Items exempted from tax 
     * 
     * @var array
     * @access private
     */
    private $exemptedItems = array(
            'books'    => array('book'),
            'foods'    => array('chocolate', 'apples'),
            'medicine' => array('pills', 'drugs')
    );

    /**
     * total 
     * 
     * @var float
     * @access private
     */
    private $total = 0;

    /**
     * totalTax 
     * 
     * @var float
     * @access private
     */
    private $totalTax = 0;

    /**
     * Sales tax hard coded at 10% 
     * 
     * @var float
     * @access private
     */
    private $salesTax = 0.10;

    /**
     * Import tax hard coded to 5% 
     * 
     * @var float
     * @access private
     */
    private $importTax = 0.05;

    /**
     * Imported product pattern
     * 
     * @var string
     * @access private
     */
    private $importPattern = '/import/i';

    /**
     * asCSV 
     * 
     * @param string $filepath 
     * @access public
     * @return void
     */
    public function asCSV($filepath = '') 
    {
        if ($filepath !== '') {
            return file_put_contents($filepath, $this->render());
        }  else {
            return $this->render();
        }
    }

    /**
     * forInput 
     * 
     * @param string $input 
     * @access public
     * @return void
     */
    public function forInput($input = '') 
    {
        if ($input === '') 
            throw new SalesTaxException('Expected input');

        $this->input = $input;
        $this->parse();
        $this->calculate();

        return $this;
    }

    /**
     * Renders the data 
     * 
     * @access private
     * @return string
     */
    private function render()
    {
        if (count($this->data) === 0)
            throw new SalesTaxExemption("Invalid data. Data required");

        $buffer = '';
        foreach ($this->data as $items) {
            $buffer .= sprintf("%s, %s, %s\n", $items['Quantity'], 
                                               $items['Product'], 
                                               number_format($items['Price'], 2));
        }

        $buffer .= sprintf("\nSales Taxes: %s\n", $this->totalTax);
        $buffer .= sprintf("Total: %s",         $this->total);

        return $buffer; 
    }

    /**
     * Calculates the tax of the data 
     * 
     * @access private
     * @return void
     */
    private function calculate()
    {
        if (count($this->data) === 0)
            throw new SalesTaxException("Invalid data. Data required");

        $roundValue = function($value) {
            return number_format((ceil($value / 0.05) * 0.05), 2);
        };

        $total    = 0;
        $totalTax = 0;

        $dataCount = count($this->data);

        for ($k = 0; $k < $dataCount; $k++ ) {
            $salesTax  = 0; 
            $importTax = 0;      

            // apply sales tax
            if (!$this->checkExemption($this->data[$k]['Product']))
                $salesTax = $this->data[$k]['Price'] * $this->salesTax;

            // apply import tax
            if ($this->checkImport($this->data[$k]['Product']))
                $importTax = $this->data[$k]['Price'] * $this->importTax;

            $itemTax                  = $roundValue($salesTax + $importTax);
            $this->data[$k]['Price'] += $itemTax;

            $totalTax += $itemTax * $this->data[$k]['Quantity']; 
            $total    += $this->data[$k]['Price'] * $this->data[$k]['Quantity'];

        }

        $this->total    = number_format($total,    2);
        $this->totalTax = number_format($totalTax, 2);
    }

    /**
     * Parses input
     * 
     * @access private
     * @return void
     */
    private function parse() 
    {
        $lines      = explode("\n", $this->input);
        $columns    = array();
        $this->data = array();

        foreach ($lines as $lineNumber => $line) {
            $lineValues = explode(',', $line);

            if (count($columns) === 0) {
                // header row expected
                foreach ($lineValues as $index => $value) {
                    if (!isset($this->fields[trim($value)]))
                        throw new SalesTaxException("Unexpected column value: $value");

                    $columns[$index] = trim($value);
                }
                continue;
            }

            $lineData = array();
            foreach ($columns as $columnIndex => $column) {
                if (!isset($lineValues[$columnIndex]) ||
                        !$this->validate($lineValues[$columnIndex], $column)
                   ) {
                    throw new SalesTaxException("Missing or invalid value for field $column on line " . ++$lineNumber);
                }

                $lineData[$column] = trim($lineValues[$columnIndex]);
            }

            $this->data[] = $lineData;
        }
    }

    /**
     * Checks whether a product is exempted from tax 
     * 
     * @param string $product 
     * @access private
     * @return boolean
     */
    private function checkExemption($product = '') 
    {
        foreach ($this->exemptedItems as $itemCategory) {
            foreach ($itemCategory as $item) {
                if (preg_match("/$item/i", $product))
                    return true;
            }           
        }   

        return false;     
    }

    /**
     * Checks whether goods are imported
     * 
     * @param string $product 
     * @access private
     * @return boolean
     */
    private function checkImport($product = '')
    {
        if (preg_match($this->importPattern, $product))
            return true;

        return false;
    }


    /**
     * Validates a field 
     * 
     * @param string $value 
     * @param string $field 
     * @access private
     * @return boolean
     */
    private function validate($value = '', $field = '') 
    {
        if (!isset($this->fields[$field]))
            throw new SalesTaxException("Validation failed. Invalid field: $field");

        if (preg_match($this->fields[$field], trim($value)))
            return true;

        return false;
    }
}
