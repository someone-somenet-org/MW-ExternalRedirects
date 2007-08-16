<?php
$wgHooks['ArticleAfterFetchContent'][] = 'ExternalRedirect';

function getTargetInfo( $article )
{
	# get configuration from LocalSettings.php
	global $wgExternalRedirectProtocols;
	$preg_protos = '(' . implode( '|', $wgExternalRedirectProtocols ) . ')';
	$preg_expr = '/^#REDIRECT \[\[(' . $preg_protos . '.*)\]\]$/';
	$num = preg_match( $preg_expr, $article->mContent, $matches);
	$targetInfo = explode( '|', $matches[1] );
	$target = $targetInfo[0];
	$targetText = $targetInfo[1];
	return array( $num, $target, $targetText );
}

function ExternalRedirect( $article, $content )
{
	global $wgEnableExternalRedirects;
	if ( $wgEnableExternalRedirects != True )
		return;
	if ( $article->mIsRedirect != 1 )
		return;
	
	# determine if this is an external redirect and determine target
	$targetInfo = getTargetInfo( $article );
	$num = $targetInfo[0];
	$target = $targetInfo[1];
	$targetText = $targetInfo[2];

	# the redirect-link doesn't start with any of the protocols:
	if ( $num == 0 ) 
		return;

	# get some important variables:
	global $wgRequest, $wgOut;
	$requestValues = $wgRequest->getValues();

	# sometimes we don't want to redirect.
	# if an action is defined (i.e. when we edit a page!):
	if ( array_key_exists('action', $requestValues) ) 
		return;

	# if redirect=no is given and we view the redirect:
	if ( array_key_exists('redirect', $requestValues) ) {
		global $wgStylePath, $wgScriptPath;
		$article->mContent = ''; # clear content

		# add our own CSS (empty for now):
		$wgOut->addHeadItem('ExternalRedirect.css', '<style type="text/css">
			@import ' . $wgScriptPath . 
			'/extensions/ExternalRedirects/ExternalRedirects.css </style>');

		# that arrow-image:
		$img_src = $wgStylePath . '/common/images/redirectltr.png';
		$img = '<img src="' . $img_src . '" alt="#REDIRECT" />';
		if ( $targetText == '' ) # use target for targetText if no TargetText found"
			$targetText = $target;
		# compose the link:
		$link = '<a href="'. $target . '" class="external text" title="'
			. $targetText . '" rel="nofollow">' . $targetText . '</a>';

		# actually add that arrow plus link to target
		$wgOut->addHTML($img . $link);
		return;
	}

	# we actually do a redirect:
	$wgOut->redirect( $target );
}
?>
