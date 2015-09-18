<?php
class Sunpop_News_Block_Adminhtml_Category_Grid_Column_Renderer_SubCategories extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        if ($row->getData('level') == 1) {
            return '<span>&nbsp&nbsp&nbsp&nbsp'.$value.'</span>';
        } else if ($row->getData('level') == 2) {
            return '<span>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$value.'</span>';
        } else if ($row->getData('level') == 3) {
            return '<span>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$value.'</span>';
        } else if ($row->getData('level') == 4) {
            return '<span>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$value.'</span>';
        } else if ($row->getData('level') == 5) {
            return '<span>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.$value.'</span>';
        } else {
            return $value;
        }

    }
}
?>
