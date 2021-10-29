<?php

namespace App\Xml;

/**
 * @author
 * Web Design Enterprise
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

class FeedItem
{
    private $elements = []; //Collection of feed elements
    private $version;

    /**
     * Constructor
     *
     * @param    contant     (RSS1/RSS2/ATOM) RSS2 is default.
     * @param mixed $version
     */
    public function __construct($version = RSS2)
    {
        $this->version = $version;
    }

    /**
     * Add an element to elements array
     *
     * @access   public
     * @param    srting  The tag name of an element
     * @param    srting  The content of tag
     * @param    array   Attributes(if any) in 'attrName' => 'attrValue' format
     * @param mixed $elementName
     * @param mixed $content
     * @param null|mixed $attributes
     * @return   void
     */
    public function addElement($elementName, $content, $attributes = null)
    {
        $this->elements[$elementName]['name'] = $elementName;
        $this->elements[$elementName]['content'] = $content;
        $this->elements[$elementName]['attributes'] = $attributes;
    }

    /**
     * Set multiple feed elements from an array.
     * Elements which have attributes cannot be added by this method
     *
     * @access   public
     * @param    array   array of elements in 'tagName' => 'tagContent' format.
     * @param mixed $elementArray
     * @return   void
     */
    public function addElementArray($elementArray)
    {
        if (!is_array($elementArray)) {
            return;
        }
        foreach ($elementArray as $elementName => $content) {
            $this->addElement($elementName, $content);
        }
    }

    /**
     * Return the collection of elements in this feed item
     *
     * @access   public
     * @return   array
     */
    public function getElements()
    {
        return $this->elements;
    }

    // Wrapper functions ------------------------------------------------------

    /**
     * Set the 'dscription' element of feed item
     *
     * @access   public
     * @param    string  The content of 'description' element
     * @param mixed $description
     * @return   void
     */
    public function setDescription($description)
    {
        $tag = ($this->version == ATOM) ? 'summary' : 'description';
        $this->addElement($tag, $description);
    }

    /**
     * @desc     Set the 'title' element of feed item
     * @access   public
     * @param    string  The content of 'title' element
     * @param mixed $title
     * @return   void
     */
    public function setTitle($title)
    {
        $this->addElement('title', $title);
    }

    /**
     * Set the 'date' element of feed item
     *
     * @access   public
     * @param    string  The content of 'date' element
     * @param mixed $date
     * @return   void
     */
    public function setDate($date)
    {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }
        if ($this->version == ATOM) {
            $tag = 'updated';
            $value = date(DATE_ATOM, $date);
        } elseif ($this->version == RSS2) {
            $tag = 'pubDate';
            $value = date(DATE_RSS, $date);
        } else {
            $tag = 'dc:date';
            $value = date("Y-m-d", $date);
        }

        $this->addElement($tag, $value);
    }

    /**
     * Set the 'link' element of feed item
     *
     * @access   public
     * @param    string  The content of 'link' element
     * @param mixed $link
     * @return   void
     */
    public function setLink($link)
    {
        if ($this->version == RSS2 || $this->version == RSS1) {
            $this->addElement('link', $link);
        } else {
            $this->addElement('link', '', ['href' => $link]);
            $this->addElement('id', FeedWriter::uuid($link, 'urn:uuid:'));
        }
    }

    /**
     * Set the 'encloser' element of feed item
     * For RSS 2.0 only
     *
     * @access   public
     * @param    string  The url attribute of encloser tag
     * @param    string  The length attribute of encloser tag
     * @param    string  The type attribute of encloser tag
     * @param mixed $url
     * @param mixed $length
     * @param mixed $type
     * @return   void
     */
    public function setEncloser($url, $length, $type)
    {
        $attributes = [
            'url' => $url,
            'length' => $length,
            'type' => $type
        ];

        $this->addElement('enclosure', '', $attributes);
    }
}
