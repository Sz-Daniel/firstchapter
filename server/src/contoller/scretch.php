<?php
function scretchHandler()
{
    /**
     * Place to trying new functions
     */

     echo render("wrapper.phtml",[
        'content' => render("scretch.phtml",[
        ])
    ]);
}
