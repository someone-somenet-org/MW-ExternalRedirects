<?php

class ExternalRedirects extends QueryPage {
    function __construct() {
        /**
         * We do not call parent::__construct here, because this does
         * not work with classes extending QueryPage. Instead we add an
         * array to $wgQueryPage in ExternalRedirects.php
         */
        parent::__construct('ExternalRedirects');
        $this->mName = 'ExternalRedirects';
    }

    function execute($par) {
        global $wgExternalRedirectsEnableSpecialPage, $wgOut;
        if (! $wgExternalRedirectsEnableSpecialPage)
            return;

        $wgOut->setPagetitle(wfMsg('externalredirects'));
        $this->setHeaders();
        list($limit, $offset) = wfCheckLimits();
        $this->doQuery($offset, $limit);
    }

    function setHeaders() {
            global $wgOut;
            $wgOut->setArticleRelated(false);
            $wgOut->setRobotPolicy("noindex,nofollow");
            $wgOut->setPageTitle($this->getDescription());
    }


    function getRedirect($par) {
        return;
    }
    function isListed() {
        global $wgExternalRedirectsEnableSpecialPage;
        if (! $wgExternalRedirectsEnableSpecialPage)
            return false;
        else
            return true;
    }
    function isRestricted() { return false; }

    function userCanExecute(User $user) {
        global $wgExternalRedirectsEnableSpecialPage;
        if (! $wgExternalRedirectsEnableSpecialPage)
            return false;
        else
            return true;
    }
    function getDescription() { return wfMsg('mExternalRedirectsDescription') ; }
    function including($x = NULL) { return false; }

        function getName() {
                return wfMsgForContent('externalredirects');
        }

    function getLocalName() {
                return wfMsg('externalredirects');
        }

        function isExpensive() { return true; }
        function isSyndicated() { return false; }

        function getOrder() {
                return '';
        }

        function getSQL() {
        global $wgExternalRedirectProtocols;
        $expr = '^(';
        $arr = array();
        foreach($wgExternalRedirectProtocols as $proto) {
            $arr[] = ucfirst($proto);
        }
        $expr .= implode('|', $arr) . '://)';

                $dbr = wfGetDB(DB_SLAVE);
                list($page, $redirect) = $dbr->tableNamesN('page', 'redirect');

                $sql = "SELECT 'BrokenRedirects'  AS type,
                                p1.page_namespace AS namespace,
                                p1.page_title     AS title,
                                rd_namespace,
                                rd_title
                           FROM $redirect AS rd
                   JOIN $page p1 ON (rd.rd_from=p1.page_id)
                      LEFT JOIN $page AS p2 ON (rd_namespace=p2.page_namespace AND rd_title=p2.page_title)
                                  WHERE rd_namespace >= 0
                                    AND p2.page_namespace IS NULL
                    AND rd_title REGEXP '" . $expr . "'";
                return $sql;
        }

    function outputResults($out, $skin, $dbr, $res, $num, $offset) {
        global $wgRequest;
        if ($wgRequest->getText('action') == "raw") {
            if($num > 0) {
                for($i = 0; $i < $num && $row = $dbr->fetchObject($res); $i++) {
                    $toObj = $row->rd_title;
                    $firstLetter = substr($toObj, 0, 1);
                    $rest = substr($toObj, 1);
                    $toObj = strtolower($firstLetter) . $rest;
                    print $row->title . "\t" . $toObj . "\n";
                }
            }
            die();
        } else {
            QueryPage::outputResults($out, $skin, $dbr, $res, $num, $offset);
        }
    }

    function formatResult($skin, $result) {
        global $wgUser, $wgContLang;

        $fromObj = Title::makeTitle($result->namespace, $result->title);
        if (isset($result->rd_title)) {
            $toObj = $result->rd_title;
            $firstLetter = substr($toObj, 0, 1);
            $rest = substr($toObj, 1);
            $toObj = strtolower($firstLetter) . $rest;

            if (! textIsRedirect ($toObj)) return;
        } else {
            $blinks = $fromObj->getBrokenLinksFrom(); # TODO: check for redirect, not for links
            if ($blinks) {
                $toObj = $blinks[0];
            $firstLetter = substr($toObj, 0, 1);
            $rest = substr($toObj, 1);
            $toObj = strtolower($firstLetter) . $rest;
                    } else {
                            $toObj = false;
                    }
            }

            $from = $skin->link($fromObj ,'', array(),
                                array('redirect' => 'no'));
            $edit = $skin->link($fromObj, wfMsgHtml('brokenredirects-edit'),
                                array('action' => 'edit'));
            $to   = $skin->makeExternalLink($toObj, $toObj);
            $arr = $wgContLang->getArrow();

            $out = "{$from} {$edit}";

            if($wgUser->isAllowed('delete')) {
                $delete = $skin->link($fromObj,
                    wfMsgHtml('brokenredirects-delete'),
                    array(),
                    array('action' => 'delete')
                );
                $out .= " {$delete}";
            }

            $out .= " {$arr} {$to}";
            return $out;
        }

}

