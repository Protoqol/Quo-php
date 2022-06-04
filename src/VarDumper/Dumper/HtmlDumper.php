<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Protoqol\Quo\VarDumper\Dumper;

use Protoqol\Quo\VarDumper\Cloner\Cursor;
use Protoqol\Quo\VarDumper\Cloner\Data;

/**
 * HtmlDumper dumps variables as HTML.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class HtmlDumper extends CliDumper
{
    public static $defaultOutput = 'php://output';

    protected static $themes = [
        'dark'  => [
            'default'   => 'background-color:#18171B; color:#FF8400; line-height:1.2em; font:12px Menlo, Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-all',
            'num'       => 'font-weight:bold; color:#1299DA',
            'const'     => 'font-weight:bold',
            'str'       => 'font-weight:bold; color:#56DB3A',
            'note'      => 'color:#1299DA',
            'ref'       => 'color:#A0A0A0',
            'public'    => 'color:#FFFFFF',
            'protected' => 'color:#FFFFFF',
            'private'   => 'color:#FFFFFF',
            'meta'      => 'color:#B729D9',
            'key'       => 'color:#56DB3A',
            'index'     => 'color:#1299DA',
            'ellipsis'  => 'color:#FF8400',
            'ns'        => 'user-select:none;',
        ],
        'light' => [
            'default'   => 'background:none; color:#CC7832; line-height:1.2em; font:12px Menlo, Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-all',
            'num'       => 'font-weight:bold; color:#1299DA',
            'const'     => 'font-weight:bold',
            'str'       => 'font-weight:bold; color:#629755;',
            'note'      => 'color:#6897BB',
            'ref'       => 'color:#6E6E6E',
            'public'    => 'color:#262626',
            'protected' => 'color:#262626',
            'private'   => 'color:#262626',
            'meta'      => 'color:#B729D9',
            'key'       => 'color:#789339',
            'index'     => 'color:#1299DA',
            'ellipsis'  => 'color:#CC7832',
            'ns'        => 'user-select:none;',
        ],
    ];

    protected $dumpHeader;
    protected $dumpPrefix     = '<pre class=quo-dump id=%s data-indent-pad="%s">';
    protected $dumpSuffix     = '</pre>';
    protected $dumpId         = 'quo-dump';
    protected $colors         = true;
    protected $headerIsDumped = false;
    protected $lastDepth      = -1;
    protected $styles;

    private $displayOptions      = [
        'maxDepth'        => 1,
        'maxStringLength' => 160,
        'fileLinkFormat'  => null,
    ];
    private $extraDisplayOptions = [];

    private $data;

    /**
     * {@inheritdoc}
     */
    public function __construct($output = null, string $charset = null, int $flags = 0)
    {
        AbstractDumper::__construct($output, $charset, $flags);
        $this->dumpId                           = 'quo-dump-' . mt_rand();
        $this->displayOptions['fileLinkFormat'] = ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
        $this->styles                           = static::$themes['dark'] ?? self::$themes['dark'];
    }

    /**
     * {@inheritdoc}
     */
    public function setStyles(array $styles)
    {
        $this->headerIsDumped = false;
        $this->styles         = $styles + $this->styles;
    }

    public function setTheme(string $themeName)
    {
        if (!isset(static::$themes[$themeName])) {
            throw new \InvalidArgumentException(sprintf('Theme "%s" does not exist in class "%s".', $themeName, static::class));
        }

        $this->setStyles(static::$themes[$themeName]);
    }

    /**
     * Configures display options.
     *
     * @param array $displayOptions A map of display options to customize the behavior
     */
    public function setDisplayOptions(array $displayOptions)
    {
        $this->headerIsDumped = false;
        $this->displayOptions = $displayOptions + $this->displayOptions;
    }

    /**
     * Sets an HTML header that will be dumped once in the output stream.
     */
    public function setDumpHeader(?string $header)
    {
        $this->dumpHeader = $header;
    }

    /**
     * Sets an HTML prefix and suffix that will encapse every single dump.
     */
    public function appendDumpPrefix(string $prefix)
    {
        $this->dumpPrefix .= $prefix;
    }

    /**
     * Sets an HTML prefix and suffix that will encapse every single dump.
     */
    public function setDumpBoundaries(string $prefix, string $suffix)
    {
        $this->dumpPrefix = $prefix;
        $this->dumpSuffix = $suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(Data $data, $output = null, array $extraDisplayOptions = [])
    {
        $this->extraDisplayOptions = $extraDisplayOptions;
        $result                    = parent::dump($data, $output);
        $this->dumpId              = 'quo-dump-' . mt_rand();
        $this->data                = $data;

        return $result;
    }

    /**
     * Dumps the HTML header.
     */
    protected function getDumpHeader()
    {
        $this->headerIsDumped = $this->outputStream ?? $this->lineDumper;

        if (null !== $this->dumpHeader) {
            return $this->dumpHeader;
        }
        return $this->dumpHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function dumpString(Cursor $cursor, string $str, bool $bin, int $cut)
    {
        if ('' === $str && isset($cursor->attr['img-data'], $cursor->attr['content-type'])) {
            $this->dumpKey($cursor);
            $this->line .= $this->style('default', $cursor->attr['img-size'] ?? '', []);
            $this->line .= $cursor->depth >= $this->displayOptions['maxDepth'] ? ' <samp class=quo-dump-compact>' : ' <samp class=quo-dump-expanded>';
            $this->endValue($cursor);
            $this->line .= $this->indentPad;
            $this->line .= sprintf('<img src="data:%s;base64,%s" /></samp>', $cursor->attr['content-type'], base64_encode($cursor->attr['img-data']));
            $this->endValue($cursor);
        } else {
            parent::dumpString($cursor, $str, $bin, $cut);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function enterHash(Cursor $cursor, int $type, $class, bool $hasChild)
    {
        if (Cursor::HASH_OBJECT === $type) {
            $cursor->attr['depth'] = $cursor->depth;
        }
        parent::enterHash($cursor, $type, $class, false);

        if ($cursor->skipChildren || $cursor->depth >= $this->displayOptions['maxDepth']) {
            $cursor->skipChildren = false;
            $eol                  = ' class=quo-dump-compact>';
        } else {
            $this->expandNextHash = false;
            $eol                  = ' class=quo-dump-expanded>';
        }

        if ($hasChild) {
            $this->line .= '<samp data-depth=' . ($cursor->depth + 1);
            if ($cursor->refIndex) {
                $r = Cursor::HASH_OBJECT !== $type ? 1 - (Cursor::HASH_RESOURCE !== $type) : 2;
                $r .= $r && 0 < $cursor->softRefHandle ? $cursor->softRefHandle : $cursor->refIndex;

                $this->line .= sprintf(' id=%s-ref%s', $this->dumpId, $r);
            }
            $this->line .= $eol;
            $this->dumpLine($cursor->depth);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function leaveHash(Cursor $cursor, int $type, $class, bool $hasChild, int $cut)
    {
        $this->dumpEllipsis($cursor, $hasChild, $cut);
        if ($hasChild) {
            $this->line .= '</samp>';
        }
        parent::leaveHash($cursor, $type, $class, $hasChild, 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function style(string $style, string $value, array $attr = [])
    {
        if ('' === $value) {
            return '';
        }

        $v = esc($value);

        if ('ref' === $style) {
            if (empty($attr['count'])) {
                return sprintf('<a class=quo-dump-ref>%s</a>', $v);
            }
            $r = ('#' !== $v[0] ? 1 - ('@' !== $v[0]) : 2) . substr($value, 1);

            return sprintf('<a class=quo-dump-ref href=#%s-ref%s title="%d occurrences">%s</a>', $this->dumpId, $r, 1 + $attr['count'], $v);
        }

        if ('const' === $style && isset($attr['value'])) {
            $style .= sprintf(' title="%s"', esc(is_scalar($attr['value']) ? $attr['value'] : json_encode($attr['value'])));
        } else if ('public' === $style) {
            $style .= sprintf(' title="%s"', empty($attr['dynamic']) ? 'Public property' : 'Runtime added dynamic property');
        } else if ('str' === $style && 1 < $attr['length']) {
            $style .= sprintf(' title="%d%s characters"', $attr['length'], $attr['binary'] ? ' binary or non-UTF-8' : '');
        } else if ('note' === $style && 0 < ($attr['depth'] ?? 0) && false !== $c = strrpos($value, '\\')) {
            $style .= ' title=""';
            $attr  += [
                'ellipsis'      => \strlen($value) - $c,
                'ellipsis-type' => 'note',
                'ellipsis-tail' => 1,
            ];
        } else if ('protected' === $style) {
            $style .= ' title="Protected property"';
        } else if ('meta' === $style && isset($attr['title'])) {
            $style .= sprintf(' title="%s"', esc($this->utf8Encode($attr['title'])));
        } else if ('private' === $style) {
            $style .= sprintf(' title="Private property defined in class:&#10;`%s`"', esc($this->utf8Encode($attr['class'])));
        }
        $map = static::$controlCharsMap;

        if (isset($attr['ellipsis'])) {
            $class = 'quo-dump-ellipsis';
            if (isset($attr['ellipsis-type'])) {
                $class = sprintf('"%s quo-dump-ellipsis-%s"', $class, $attr['ellipsis-type']);
            }
            $label = esc(substr($value, -$attr['ellipsis']));
            $style = str_replace(' title="', " title=\"$v\n", $style);
            $v     = sprintf('<span class=%s>%s</span>', $class, substr($v, 0, -\strlen($label)));

            if (!empty($attr['ellipsis-tail'])) {
                $tail = \strlen(esc(substr($value, -$attr['ellipsis'], $attr['ellipsis-tail'])));
                $v    .= sprintf('<span class=%s>%s</span>%s', $class, substr($label, 0, $tail), substr($label, $tail));
            } else {
                $v .= $label;
            }
        }

        $v = "<span class=quo-dump-{$style}>" . preg_replace_callback(static::$controlCharsRx, function ($c) use ($map) {
                $s = $b = '<span class="quo-dump-default';
                $c = $c[$i = 0];
                if ($ns = "\r" === $c[$i] || "\n" === $c[$i]) {
                    $s .= ' quo-dump-ns';
                }
                $s .= '">';
                do {
                    if (("\r" === $c[$i] || "\n" === $c[$i]) !== $ns) {
                        $s .= '</span>' . $b;
                        if ($ns = !$ns) {
                            $s .= ' quo-dump-ns';
                        }
                        $s .= '">';
                    }

                    $s .= $map[$c[$i]] ?? sprintf('\x%02X', \ord($c[$i]));
                } while (isset($c[++$i]));

                return $s . '</span>';
            }, $v) . '</span>';

        if (isset($attr['file']) && $href = $this->getSourceLink($attr['file'], $attr['line'] ?? 0)) {
            $attr['href'] = $href;
        }
        if (isset($attr['href'])) {
            $target = isset($attr['file']) ? '' : ' target="_blank"';
            $v      = sprintf('<a href="%s"%s rel="noopener noreferrer">%s</a>', esc($this->utf8Encode($attr['href'])), $target, $v);
        }
        if (isset($attr['lang'])) {
            $v = sprintf('<code class="%s">%s</code>', esc($attr['lang']), $v);
        }

        return $v;
    }

    /**
     * {@inheritdoc}
     */
    protected function dumpLine(int $depth, bool $endOfValue = false)
    {
        if (-1 === $this->lastDepth) {
            $this->line = sprintf($this->dumpPrefix, $this->dumpId, $this->indentPad) . $this->line;
        }
        if ($this->headerIsDumped !== ($this->outputStream ?? $this->lineDumper)) {
            $this->line = $this->getDumpHeader() . $this->line;
        }

        if (-1 === $depth) {
            $args = ['"' . $this->dumpId . '"'];
            if ($this->extraDisplayOptions) {
                $args[] = json_encode($this->extraDisplayOptions, \JSON_FORCE_OBJECT);
            }
            // Replace is for BC
            $this->line .= sprintf(str_replace('"%s"', '%s', $this->dumpSuffix), implode(', ', $args));
        }
        $this->lastDepth = $depth;

        $this->line = mb_encode_numericentity($this->line, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');

        if (-1 === $depth) {
            AbstractDumper::dumpLine(0);
        }
        AbstractDumper::dumpLine($depth);
    }

    private function getSourceLink(string $file, int $line)
    {
        $options = $this->extraDisplayOptions + $this->displayOptions;

        if ($fmt = $options['fileLinkFormat']) {
            return \is_string($fmt) ? strtr($fmt, ['%f' => $file, '%l' => $line]) : $fmt->format($file, $line);
        }

        return false;
    }
}

function esc(string $str)
{
    return htmlspecialchars($str, \ENT_QUOTES, 'UTF-8');
}
