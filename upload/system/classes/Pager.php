<?php
/**
 * Класс взят отсюда и немного доработан под нужды проекта  https://github.com/flexocms/flexo1.source/blob/master/cms/helpers/Pager.php
 * Simple Pager helper based on the Kohana pagination helper.
 *
 * @author    Maslakov Alexander <jmas.ukraine@gmail.com>
 * @author    Kohana helper
 * @license    http://kohanaphp.com/license.html
 */

class Pager extends core
{
    // Link templates
    protected $link_tag = '<a href=":link">:name</a>';
    protected $current_tag = '<span class="current">:name</span>';
    protected $prev_tag = '<a href=":link" class="prev">&lsaquo; Назад</a>';
    protected $prev_text_tag = '<span class="prev">&lsaquo;</span>';
    protected $next_tag = '<a href=":link" class="next">Далее &rsaquo;</a>';
    protected $next_text_tag = '<span class="next">&rsaquo;</span>';
    protected $first_tag = '<a href=":link" class="first">&laquo;</a>';
    protected $last_tag = '<a href=":link" class="last">&raquo;</a>';
    protected $extended_pageof = 'Page :current_page of :total_pages';
    protected $extended_itemsof = 'Showing items :current_first_item &mdash; :current_last_item of :total_items';

    // Config values
    protected $base_url = '?';
    protected $items_per_page = 15;
    protected $total_items = 0;
    protected $query_string = 'page';
    protected $style = 'classic';
	protected $element_id = 'pager';
	protected $css_class = 'pager';

    // Autogenerated values
    protected $url;
    protected $current_page;
    protected $total_pages;
    protected $current_first_item;
    protected $current_last_item;
    protected $first_page;
    protected $last_page;
    protected $previous_page;
    protected $next_page;
    protected $sql_offset;
    protected $sql_limit;

    /**
     * Constructs a new Pager object.
     *
     * @param   array  configuration settings
     * @return  void
     */
    function __construct($config = array())
    {
        $this->initialize($config);
    }

    /**
     * Sets config values.
     *
     * @param   array  configuration settings
     * @return  void
     */
    function initialize($config = array())
    {
        foreach ($config as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->$key = $value;
            }
        }

		if( $this->url == null )
			$this->url = $this->base_url;

		$this->total_items        = (int) max(0, $this->total_items);

        // Extract current page
		if( $this->current_page == null )
			$this->current_page = isset($_GET[$this->query_string]) ? (int) $_GET[$this->query_string] : 1;

        // Insert {page} placeholder
        $_GET[$this->query_string] = '{page}';

        // Create full URL
		if( $this->url == null )
			$this->url = $this->url . str_replace('%7Bpage%7D', '{page}', '?' . http_build_query($_GET));

        // Reset page number
        $_GET[$this->query_string] = $this->current_page;

        $this->items_per_page     = (int) max(1, $this->items_per_page);
        $this->total_pages        = (int) ceil($this->total_items / $this->items_per_page);
        $this->current_page       = (int) min(max(1, $this->current_page), max(1, $this->total_pages));
        $this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
        $this->current_last_item  = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);

        $this->first_page         = ($this->current_page === 1) ? FALSE : 1;
        $this->last_page          = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
        $this->previous_page      = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
        $this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;

        $this->sql_offset         = (int) ($this->current_page - 1) * $this->items_per_page;
        $this->sql_limit          = sprintf(' LIMIT %d OFFSET %d ', $this->items_per_page, $this->sql_offset);
    }

    /**
     * Generates the HTML for the chosen pagination style.
     *
     * @return  string  pagination html
     */
    public function render()
    {
        $out = '<p id="'. $this->element_id .'" class="'. $this->css_class .'">'."\n";

        switch($this->style)
        {
            // � First  < 1 2 3 >  Last �
            case 'classic':
            default:

                if($this->first_page){
                    $out .= str_replace(':link', str_replace('{page}', 1, $this->url), $this->first_tag)."\n";
                }

                if($this->previous_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->previous_page, $this->url), $this->prev_tag)."\n";
                }

                for ($i = 1; $i <= $this->total_pages; $i++)
                {
                    if ($i == $this->current_page) {
                        $out .= str_replace(':name', $i, $this->current_tag)."\n";
                    }else {
                        $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $i, $this->url), $i), $this->link_tag)."\n";
                    }
                }

                if($this->next_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->next_page, $this->url), $this->next_tag)."\n";
                }

                if($this->last_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->last_page, $this->url), $this->last_tag)."\n";
                }

                break;

            // � Previous  1 2 � 5 6 7 8 9 10 11 12 13 14 � 25 26  Next �
            case 'digg':

                if($this->previous_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->previous_page, $this->url), $this->prev_tag)."\n";
                }

                if ($this->total_pages < 13) /* � Previous  1 2 3 4 5 6 7 8 9 10 11 12  Next � */
                {
                    for ($i = 1; $i <= $this->total_pages; $i++){
                        if ($i == $this->current_page){
                            $out .= str_replace(':name', $i, $this->current_tag)."\n";
                        }else{
                            $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $i, $this->url), $i), $this->link_tag)."\n";
                        }
                    }
                }
                elseif ($this->current_page < 9) /* � Previous  1 2 3 4 5 6 7 8 9 10 � 25 26  Next � */
                {
                    for ($i = 1; $i <= 10; $i++){
                        if ($i == $this->current_page){
                            $out .= str_replace(':name', $i, $this->current_tag)."\n";
                        }else{
                            $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $i, $this->url), $i), $this->link_tag)."\n";
                        }
                    }

                    $out .= '&hellip;'."\n";

                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $this->total_pages - 1, $this->url), $this->total_pages - 1), $this->link_tag)."\n";
                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $this->total_pages, $this->url), $this->total_pages), $this->link_tag)."\n";
                }
                elseif ($this->current_page > $this->total_pages - 8) /* � Previous  1 2 � 17 18 19 20 21 22 23 24 25 26  Next � */
                {
                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', 1, $this->url), 1), $this->link_tag)."\n";
                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', 2, $this->url), 2), $this->link_tag)."\n";

                    $out .= '&hellip;'."\n";

                    for ($i = $this->total_pages - 9; $i <= $this->total_pages; $i++){
                        if ($i == $this->current_page){
                            $out .= str_replace(':name', $i, $this->current_tag)."\n";
                        }else{
                            $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $i, $this->url), $i), $this->link_tag)."\n";
                        }
                    }
                }
                else /* � Previous  1 2 � 5 6 7 8 9 10 11 12 13 14 � 25 26  Next � */
                {

                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', 1, $this->url), 1), $this->link_tag)."\n";
                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', 2, $this->url), 2), $this->link_tag)."\n";

                    $out .= '&hellip;'."\n";

                    for ($i = $this->current_page - 5; $i <= $this->current_page + 5; $i++){
                        if ($i == $this->current_page){
                            $out .= str_replace(':name', $i, $this->current_tag)."\n";
                        }else{
                            $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $i, $this->url), $i), $this->link_tag)."\n";
                        }
                    }

                    $out .= '&hellip;'."\n";

                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $this->total_pages - 1, $this->url), $this->total_pages - 1), $this->link_tag)."\n";
                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $this->total_pages, $this->url), $this->total_pages), $this->link_tag)."\n";
                }

                if($this->next_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->next_page, $this->url), $this->next_tag)."\n";
                }

                break;

            // � Previous | Page 2 of 11 | Showing items 6-10 of 52 | Next �
            case 'extended':

                if ($this->previous_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->previous_page, $this->url), $this->prev_tag)."\n";
                }else{
                    $out .= $this->prev_text_tag;
                }

                $out .= ' <span class="vertical">|</span> ' . str_replace(array(':current_page', ':total_pages'), array($this->current_page, $this->total_pages), $this->extended_pageof).
                        ' <span class="vertical">|</span> ' . str_replace(array(':current_first_item', ':current_last_item', ':total_items'), array($this->current_first_item, $this->current_last_item, $this->total_items), $this->extended_itemsof).
                        ' <span class="vertical">|</span> '."\n";

                if ($this->next_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->next_page, $this->url), $this->next_tag)."\n";
                }else{
                    $out .= $this->next_text_tag;
                }

                break;

            // Pages: 1 � 4 5 6 7 8 � 15
            case 'punbb':

                if ($this->current_page > 3)
                {
                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', 1, $this->url), 1), $this->link_tag)."\n";
                    if ($this->current_page != 4) $out .= '&hellip;'."\n";
                }

                for ($i = $this->current_page - 2, $stop = $this->current_page + 3; $i < $stop; ++$i)
                {
                    if ($i < 1 OR $i > $this->total_pages) continue;

                    if ($this->current_page == $i){
                        $out .= str_replace(':name', $i, $this->current_tag)."\n";
                    }else{
                        $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $i, $this->url), $i), $this->link_tag)."\n";
                    }

                }

                if ($this->current_page <= $this->total_pages - 3)
                {
                    if ($this->current_page != $this->total_pages - 3) $out .= '&hellip;'."\n";
                    $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $this->total_pages, $this->url), $this->total_pages), $this->link_tag)."\n";
                }

                break;

			// Pages: 15 14 13 ... 3 2 1
			case 'back':

				if($this->next_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->next_page, $this->url), $this->prev_tag)."\n";
                }

                for( $i = $this->total_pages, $stop = 0; $i > $stop; --$i )
				{
					if ($this->current_page == $i){
                        $out .= str_replace(':name', $i, $this->current_tag)."\n";
                    }else{
                        $out .= str_replace(array(':link', ':name'), array(str_replace('{page}', $i, $this->url), $i), $this->link_tag)."\n";
                    }
				}

				if($this->previous_page){
                    $out .= str_replace(':link', str_replace('{page}', $this->previous_page, $this->url), $this->next_tag)."\n";
                }

				break;
        }

        return $out . '</p>'."\n";
    }

    /**
     * Magically converts Pagination object to string.
     *
     * @return  string  pagination html
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Magically gets a pagination variable.
     *
     * @param   string  variable key
     * @return  mixed   variable value if the key is found
     * @return  void    if the key is not found
     */
    public function __get($key)
    {
        if (isset($this->$key))
            return $this->$key;
    }

    /**
     * Adds a secondary interface for accessing properties, e.g. $pager->total_pages().
     * Note that $pagination->total_pages is the recommended way to access properties.
     *
     * @param   string  function name
     * @return  string
     */
    public function __call($func, $args = NULL)
    {
        return $this->__get($func);
    }

} // End Pager Class

?>