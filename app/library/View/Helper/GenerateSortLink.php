<?php
namespace App\View\Helper;

class GenerateSortLink extends BaseHelper
{
    /**
     * @param string $text This is the text to show in the link
     * @param string $column The column in the db
     */
    function __invoke($text, $columnName, $selectedColumn=null)
    {
        $request = $this->container->get('request');

        // get the $_GET params
        $query = $request->getQueryParams();

        // put sort property at start
        $query['sort'] = $columnName;

        // return to page 1
        $query['page'] = 1;

        // toggle query

        // always start from the same dir for new columns
        if ($selectedColumn != $columnName) {
            $query['dir'] = 1;
        } elseif (isset($query['dir'])) {
            if ($query['dir'] == 1) {
                $query['dir'] = -1;
            } else {
                $query['dir'] = 1;
            }
        } else {
            $query['dir'] = 1;
        }

        if ($selectedColumn && $selectedColumn == $columnName) {
            if ($query['dir'] > 0) {
                $icon = ' <i class="fa fa-caret-down" aria-hidden="true"></i>';
            } elseif ($query['dir'] < 0) {
                $icon = ' <i class="fa fa-caret-up" aria-hidden="true"></i>';
            }
        }

        // return the link html
        return '<a href="?' . http_build_query($query) . '">' . $text . ((isset($icon)) ? $icon : '') . '</a>';
    }
}
