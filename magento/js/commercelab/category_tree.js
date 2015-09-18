/**
 * @category    CommerceLab
 * @package     CommerceLab_CategoryTree
 * @author Uvarov Yurij <zim32@ukr.net>
 */
jQuery.noConflict();

jQuery(document).ready(function($){

    $('#commercelab_categories_div').treeview(
            {
                collapsed: true
            }
                                                        );
});
