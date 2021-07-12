<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\XM1Rray
 * - Autoload, application dependencies
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Sammy\Packs\XM1Rray {
  /**
   * Make sure the module base internal class is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!class_exists('Sammy\Packs\XM1Rray\Base')){
  /**
   * @class Base
   * Base internal class for the
   * XM1Rray module.
   * -
   * This is (in the ils environment)
   * an instance of the php module,
   * wich should contain the module
   * core functionalities that should
   * be extended.
   * -
   * For extending the module, just create
   * an 'exts' directory in the module directory
   * and boot it by using the ils directory boot.
   * -
   */
  class Base {

    public function parse_string ($string = null) {

      $finalDocumentBody = simplexml_load_string (
        join ('', [
          '<?xml version=\'1.0\'?>',
          '<document>',
          $string,
          '</document>'
        ])
      );

      return $finalDocumentBody;
    }

    public function parse_file ($file) {
      return simplexml_load_string ($file);
    }

    private function readXMLContainer (array $array, array $originalArray = []) {
      $finalXML = '';

      if ( !$originalArray ) {
        $originalArray = $array;
      }

      foreach ($array as $key => $value) {
        $keyElementTagName = $key;

        if (is_numeric ($keyElementTagName)) {
          $keyElementTagName = 'item';
        }

        if (is_array ($value)) {

          $currentElementInOriginalArray = ( boolean ) (
            isset ($originalArray [ $key ]) &&
            is_object ($originalArray [ $key ])
          );

          if ( $currentElementInOriginalArray ) {
            $originalArrayElement = $originalArray [ $key ];

            $originalArrayElementClassNames = preg_split (
              '/\\\+/', get_class ( $originalArrayElement )
            );

            $keyElementTagName = strtolower (
              $originalArrayElementClassNames [
                -1 + count ($originalArrayElementClassNames)
              ]
            );
          }

          $finalXML .= '<' . $keyElementTagName . '>' . $this->readXMLContainer ($value) . '</' . $keyElementTagName . '>';
        } else {
          $finalXML .= '<' . $keyElementTagName . '>' . $value . '</' . $keyElementTagName . '>';
        }
      }

      return $finalXML;
    }

    public function xml_from_array (array $array = [], array $originalArray = []) {
      if ( !$array ) return null;

      if ( !$originalArray ) {
        $originalArray = $array;
      }

      $convertedArray2XML = $this->readXMLContainer (
        $array, $originalArray
      );

      $finalDocumentBody = simplexml_load_string (
        join ('', [
          '<?xml version=\'1.0\'?>',
          '<document>',
          $convertedArray2XML,
          '</document>'
        ])
      );

      return $finalDocumentBody->asXML ();
    }

    private function leanData ($data) {
      if (is_object($data)) {
        $ILinable = 'Sammy\Packs\Sami\Base\ILinable';
        $ClassImplements = class_implements (
          get_class ( $data )
        );

        if (in_array ($ILinable, $ClassImplements)){
          return $data->lean ();
        } else {
          return get_object_vars ($data);
        }
      } elseif (is_array($data)) {
        foreach ($data as $key => $value) {
          $data[ $key ] = $this->leanData (
            $value
          );
        }
      }

      return $data;
    }

  }}
}
