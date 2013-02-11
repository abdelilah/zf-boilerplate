<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Helper;

class JSObject extends BaseObject
{
    protected function init()
    {
        if ($this->getOption('multiline') && !$this->getOption('wrapper')) {
            $this->setOption('wrapper', '%s');
        }
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter\Helper.BaseObject::asCode()
     */
    public function asCode($value)
    {
        if ($value instanceof JSObject) {
            $value = (string) $value;
        } elseif (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_string($value)) {
            $value = '\''.$value.'\'';
        } elseif (is_array($value)) {
            $tmp = array();
            $useKey = !$this->isKeysNumeric($value);
            foreach ($value as $k => $v) {
                $v = $this->asCode($v);
                $tmp[] = $useKey ? sprintf('%s: %s', $k, $v) : $v;
            }
            $multiline = $this->getOption('multiline') && count($value) > 1;
            $value = ($multiline ? "\n" : '').implode($multiline ? ",\n" : ', ', $tmp).($multiline ? "\n" : '');
            if ($useKey) {
                $value = sprintf('{%s}', $value);
            } else {
                $value = sprintf('[%s]', $value);
            }
            if ($multiline) {
                $value = $this->wrapLines($value, $this->getOption('indent', 4));
            }
        }

        return $value;
    }
}