<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.tags
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

require_once cot_langfile('autotoc');

$elems = explode(',', $cfg['plugin']['autotoc']['elements']);
$text = $t->vars['PAGE_TEXT'];
$chapters = array();
$chapters_elem = array();

foreach($elems as $level => $elem)
{
	$elem = trim($elem);
	$headings = array();
	preg_match_all("`<$elem>(.*?)</$elem>`is", $text, $headings, PREG_OFFSET_CAPTURE);
	$headings = $headings[1];
	if (!$headings) continue;
	foreach($headings as $heading)
	{
		$title = $heading[0];
		$chapters[$heading[1]] = array($title, $level);
		$chapters_elem[$title] = $elem;
	}
}
ksort($chapters);

$toc = array();
$parents = array();
foreach($chapters as $chapter)
{
	list($title, $level) = $chapter;
	switch($level)
	{
		case 0:
			$toc[$title] = array();
			$parents[$level] = $title;
			break;
		case 1:
			$toc[$parents[0]][$title] = array();
			$parents[$level] = $title;
			break;
		default:
			$toc[$parents[0]][$parents[1]][$title] = array();
			break;
	}
}

function buildTOC(&$text, $chapters, $parents = '')
{
	global $chapters_elem;
	$i=0;
	$toc = '<ol style="list-style:none;">';
	foreach($chapters as $chapter_raw => $subchapters)
	{
		$chapter = strip_tags(trim($chapter_raw));
		$i++;
		$elem = $chapters_elem[$chapter_raw];
		$level = $parents.$i;
		$url = $_SERVER["REQUEST_URI"] . "#ch$level";
		$toc .= "<li><a href=\"$url\" title=\"$chapter\">$level. $chapter</a>";
		$text = str_replace("<$elem>$chapter_raw</$elem>", "<a name=\"ch$level\"></a><$elem>$level. $chapter</$elem>", $text);
		if (count($subchapters) > 0)
		{
			$toc .= buildTOC($text, $subchapters, $level.'.');
		}
		$toc .= '</li>';
	}
	$toc .= '</ol>';
	return $toc;
}

$t->assign('PAGE_TOC', buildTOC($text, $toc));
$t->assign('PAGE_TEXT', $text);

?>
