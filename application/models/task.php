<?php

/**
 * The User Model
 *
 * @author Shreyansh Goel
 */
namespace models;
class Task extends \Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label first name
     */
    protected $_email;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label first name
     */
    protected $_csv;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * 
     * @validate required
     * @label first name
     */
    protected $_days;

    /**
     * @column
     * @readwrite
     * @type boolean
     */
    protected $_status = 0;

}
