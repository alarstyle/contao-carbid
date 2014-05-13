<?php

/**
 * Carbid for Contao Open Source CMS
 *
 * Copyright (C) 2014 Alexander Stulnikov
 *
 * @package    Carbid
 * @link       https://github.com/alarstyle/contao-carbid
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


namespace Carbid;

class Helper
{

    /**
     * Get files from blob
     * @param $blob
     * @param bool $imagesOnly
     * @return array
     */
    public static function getFilesFromBlob($blob, $imagesOnly = false)
    {
        $objFiles = \FilesModel::findMultipleByUuids(deserialize($blob));
        $images = array();

        if (empty($objFiles))
            return $images;

        while ($objFiles->next())
        {
            // Skip if folder
            if ($objFiles->type !== 'file')
                continue;

            $objFile = new \File($objFiles->path, true);

            // Skip if not image if $imagesOnly flag is true
            if ($imagesOnly && !$objFile->isGdImage)
                continue;

            $meta = \Frontend::getMetaData($objFiles->meta, $GLOBALS['TL_LANGUAGE']);

            $images[] = array(
                'id'    => $objFiles->id,
                'uuid'  => $objFiles->uuid,
                'name'  => $objFile->basename,
                'path'  => $objFiles->path,
                'isImage'  => $objFile->isGdImage,
                'alt'       => !empty($meta) ? $meta['title'] : '',
                'imageUrl'  => !empty($meta) ? $meta['link']: '',
                'caption'   => !empty($meta) ? $meta['caption'] : ''
            );
        }

        return $images;
    }


    /**
     * Get the tree array from single table
     *
     * @param string  $strTable    Table name
     * @param array   $arrFields   Additional fields
     * @param integer $intPid      Parent id
     * @param integer $intDepth    Depth of the tree
     * @param array   $arrColumns  Filter columns
     * @param array   $arrValues   Additional fields
     *
     * @return array
     */
    public static function getTreeFromTable($strTable, $arrFields = array(), $intPid = 0, $intDepth = 0, $arrColumns = array(), $arrValues = array())
    {
        $items = array();
        $extraQuery = '';
        $extraFilter = '';

        if (empty($strTable))
            return $items;

        foreach($arrFields as $field) {
            $extraQuery .= 't1.'. $field .' AS '. $field .', ';
        }

        foreach($arrColumns as $i=>$column) {
            $extraFilter .= ' AND t1.'. $column .' = '. $arrValues[$i];
        }

        $objItems = \Database::getInstance()->prepare("SELECT t1.id AS id, t1.pid AS pid, $extraQuery (SELECT COUNT(*) FROM $strTable t2 WHERE t2.pid = t1.id) AS childrenCount FROM $strTable t1 WHERE t1.pid = ? $extraFilter ORDER BY t1.sorting")->execute($intPid);

        if (empty($objItems) || !$objItems->numRows)
            return $items;

        while ($objItems->next())
        {
            $item = array(
                'id'            => $objItems->id,
                'pid'           => $objItems->pid,
                'childrenCount' => $objItems->childrenCount,
                'children'      => ($objItems->childrenCount > 0 && $intDepth != 1) ? Helper::getTreeFromTable($strTable, $arrFields, $objItems->id, $intDepth > 1 ? $intDepth - 1 : 0) : array()
            );
            foreach($arrFields as $field) {
                $item[$field] = $objItems->$field;
            }
            $items[] = $item;
        }
        return $items;
    }


}
