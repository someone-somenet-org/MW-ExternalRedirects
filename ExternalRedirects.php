<?php

# register special page:
$wgAutoloadClasses['ExternalRedirects'] = __DIR__ . '/SpecialExternalRedirects.php';
$wgExtensionMessagesFiles['ExternalRedirects'] = __DIR__ . '/ExternalRedirects.i18n.php';
$wgExtensionAliasesFiles['ExternalRedirects'] = __DIR__ . '/SpecialExternalRedirects.alias.php';
$wgSpecialPages['ExternalRedirects'] = 'ExternalRedirects';
$wgHooks['ArticleAfterFetchContentObject'][] = 'fnExternalRedirect';
$wgQueryPages[] = array('ExternalRedirects', 'ExternalRedirects');

$wgExtensionCredits['other'][] = array(
    'path' => __file__,
	'name' => 'ExternalRedirects',
	'author' => 'Mathias Ertl',
	'description' => 'Allows you to use normal redirects as redirects to external websites',
	'version' => '1.5.4',
	'url' => 'http://fs.fsinf.at/wiki/ExternalRedirects',
);

function getTargetInfo($content)
{
	# get configuration from LocalSettings.php
	global $wgExternalRedirectProtocols;
	$preg_protos = '(?:' . implode("|", $wgExternalRedirectProtocols) .')';
	$preg_start = '/^#REDIRECT \[\[';
	$preg_target = '(' . $preg_protos . '[^(\]\])\|]*)';
	$preg_linktext = '(.*?(?=(?:\]\])))';
	$preg_link = $preg_target . '(?:\|' . $preg_linktext . ')?';
	$preg_end = '\]\]/i';
	$preg_expr = $preg_start . $preg_link . $preg_end;

	$num = preg_match($preg_expr, $content, $matches);
	$target = $matches[1];
	if (count($matches) >= 3) {
		$targetText = $matches[2];
	} else {
		$targetText = "";
	}
	return array($num, $target, $targetText);
}

function textIsRedirect($text) {
	global $wgExternalRedirectProtocols;
	$expr = '^(' . implode('|', $wgExternalRedirectProtocols) . ')';
	return true;
	if (ereg($expr, $text))
		return true;
	else
		return false;
}

function fnExternalRedirect($article, $content)
{
    global $wgRequest, $wgOut, $wgEnableExternalRedirects;
	if ($wgEnableExternalRedirects != True)
		return true;
	if ($article->mIsRedirect != 1)
		return true;

    $requestValues = $wgRequest->getValues();

    # sometimes we don't want to redirect.
	# if an action is defined (i.e. when we edit a page!):
	if (array_key_exists('action', $requestValues))
		return true;

    # determine if this is an external redirect and determine target
    $text = trim($content->getNativeData());
    $targetInfo = getTargetInfo($text);
	$num = $targetInfo[0];
	$target = $targetInfo[1];
	$targetText = $targetInfo[2];

	# the redirect-link doesn't start with any of the protocols:
	if ($num == 0)
        return true;

	# if redirect=no is given and we view the redirect:
    if (array_key_exists('redirect', $requestValues)) {
        global $wgStylePath, $wgScriptPath;

        # overwrite text in 1.21.0, so link doesn't get displayed twice.
        $content->mText = '';

		#remove the #REDIRECT [[http....]]
		preg_match('/^(#REDIRECT .*?(?:\]\]))/', $text, $match);
		$pattern = '/' . preg_quote($match[1], '/') . '/';
		$article->mContent = preg_replace($pattern, '', $article->mContent);

		# that arrow-image:
		$img_src = $wgStylePath . '/common/images/redirectltr.png';
		$img = '<img src="' . $img_src . '" alt="#REDIRECT" />';
		if ($targetText == '') # use target for targetText if no TargetText found"
			$targetText = $target;

		# compose the link:
		$link = '<a href="'. $target . '" class="external redirectText" title="'
			. $targetText . '" rel="nofollow">' . $targetText . '</a>';

		# actually add that arrow plus link to target
		$wgOut->addHTML($img . $link);
		return true;
	}

    # we actually do a redirect:
    $wgOut->redirect($target);

	# required for new MW-checks
	return true;
}
?>
