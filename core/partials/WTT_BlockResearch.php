<?php
global $post;
$keyword = get_post_meta($post->ID, '_wtt_post_keyword', true);
?>
<div class="wtt_box" ng-show="auth" ng-cloak>
    <div class="wtt_header">{{data.Resources.KeywordAnalysisLabel}}
        <button type="button" class="btn-collapse" ng-click="isCollapsed = !isCollapsed">
            <i class="fa" ng-class="{'fa-minus-square': !isCollapsed, 'fa-plus-square': isCollapsed}"></i>
        </button>
    </div>

    <div id="wtt_blocksearch" cg-busy="searchPromise"
         ng-class="{'display-none': isCollapsed, 'display-block': !isCollapsed}" style="padding: 5px !important">

        <div id="keyword-language-well">
            <table class="wtt-form-table">
                <tbody>
                <tr>
                    <td>
                        <div class="step-no">1. {{data.Resources.editKeywordSelectLanguageTitle}}</div>
                    </td>
                    <td>
                        <button popover-placement="auto left" uib-wtt-popover-html="htmlPopover" popover-append-to-body="true"
                                popover-trigger="mouseenter" type="button" class="btn-info"><i
                                    class="fa fa-question-circle"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="wtt-country-list">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default wtt-dropdown-toggle" data-toggle="wtt-dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"><i
                                        class="flag flag-{{searchModel.selectedSource.Country}}"></i>
                                {{searchModel.selectedSource.Name}} <span class="caret"></span></button>
                            <ul class="wtt-dropdown-menu keyword-country-list2">
                                <li ng-repeat="keywordSource in KeywordSources"
                                    ng-class="{active: searchModel.selectedSource == keywordSource}"><a
                                            href="javascript:;"
                                            ng-click="selectKeywordSource(keywordSource)"><i
                                                class="flag flag-{{keywordSource.Country}}"></i>
                                        {{keywordSource.Name}}</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="wtt-keyword" id="keyword-analysis-well">
            <div class="step-no">2. {{data.Resources.editKeywordAnalysisTitle}}
            </div>
            <div class="cl-lg-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Keyword" type="text" id="wtt-keyword"
                           name="wtt_keyword_field" autocomplete="off"
                           uib-wtt-tooltip="Fill in your keyword" tooltip-trigger="focus"
                           tooltip-placement="top" tooltip-enable="!Keyword" value="<?php echo sanitize_text_field($keyword); ?>" ng-blur="onKeywordBlur()">
                    <span class="input-group-btn">
                    <input class="wtt-btn wtt-btn-default" type="button" value="{{data.Resources.UseMyKeywordLabel}}"
                           id="wtt-keyword-check" ng-click="useMyKeyword()"
                           uib-wtt-tooltip="Start optimizing"
                           tooltip-trigger="mouseenter"
                           tooltip-placement="top"/>
                </span>
                </div>
            </div>

            <div id="keyword-suggestion-button">
                <input type="button" id="wtt-keyword-research" value="{{data.Resources.GiveSuggestionsLabel}}"
                       ng-click="submitSearchKeyword()"
                       ng-disabled="userInfo.Credits <= 0 || userInfo.TrialDays < 0"
                       uib-wtt-tooltip="{{data.Resources.SuggestionButtonTooltip}}"
                       tooltip-trigger="mouseenter"
                       tooltip-placement="top">
            </div>

            <div ng-show="searchModel.SearchResults.length > 0" id="score-analysis-area">
                <p class="lead text-center" style="margin: 5px;">
                    <em>&ldquo;{{inputKeywordResult.Keyword}}&rdquo;</em> {{data.Resources.KeywordResultLabel}}:</p>
                <div class="row" id="keyword-score-area">
                    <ul class="details-list list-unstyled list-inline" data-items-per-row="3">
                        <li>
                            <div class="score-box {{inputKeywordResult.SearchVolumeScoreAttrs.className}}"
                                 uib-wtt-popover="{{inputKeywordResult.SearchVolumeScoreAttrs.helpText}}"
                                 popover-title="{{inputKeywordResult.SearchVolumeScoreAttrs.label}}"
                                 popover-trigger="mouseenter" popover-append-to-body="true"
                                 popover-placement="top">
                                <p class="label">{{data.Resources.SearchVolumeLabel}}</p>
                                <div class="score-box-content">
                                    <p class="value"><i class="fa fa-square"></i>
                                        {{inputKeywordResult.SearchVolumeScoreAttrs.label}}
                                    </p>
                                </div>
                                <em class="sub-label">{{displaySearchVolume(inputKeywordResult.SearchVolume)}}&nbsp;</em>
                            </div>
                        </li>
                        <li>
                            <div class="score-box {{inputKeywordResult.CompetitionScoreAttrs.className}}"
                                 uib-wtt-popover="{{inputKeywordResult.CompetitionScoreAttrs.helpText}}"
                                 popover-title="{{inputKeywordResult.CompetitionScoreAttrs.label}}"
                                 popover-trigger="mouseenter" popover-append-to-body="true"
                                 popover-placement="top">
                                <p class="label">{{data.Resources.CompetitionLabel}}</p>
                                <div class="score-box-content">
                                    <p class="value"><i class="fa fa-square"></i>
                                        {{inputKeywordResult.CompetitionScoreAttrs.label}}</p>
                                </div>
                                <em class="sub-label">{{displayCompetition(inputKeywordResult.Competition)}}&nbsp;</em>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="well well-custom well-step" id="related-keywords-well"
                 ng-if="searchModel.SearchResults.length > 0">
                <h2 style="color: #8864F4; font-weight: 700; text-align: left !important;">{{data.Resources.editKeywordRelatedKeywordsTitle}}</h2>

                <div class="clearfix" id="related-keywords-wrapper">
                    <scrollable-table watch="searchModel.SearchResults">
                        <table class="table table-custom results-table" id="suggetionsTable">
                            <thead>
                            <tr>
                                <th sortable-header col="Keyword">{{data.Resources.WidgetKeywordPlaceHolder}}</th>
                                <th>&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr ng-repeat="relatedKeyword in searchModel.SearchResults">
                                <td><a href="javascript:;" class="keyBox"
                                       ng-click="useKeyword(relatedKeyword);"
                                       uib-wtt-popover-template="dynamicPopover.templateUrl"
                                       popover-placement="auto top"
                                       popover-trigger="mouseenter"
                                       popover-title="More Info"><span
                                                class="keyword">{{relatedKeyword.Keyword}}</span></a>
                                </td>
                                <script id="moreinfo.html" type="text/ng-template">
                                    <table style="100%" id="wtt_keywords_info">
                                        <tr>
                                            <td class="search-volume">
                                                <span class="interval">
                                                    <i class="fa fa-square {{relatedKeyword.SearchVolumeScoreAttrs.className}}"></i>
                                                    <strong>{{data.Resources.SearchVolumeLabel}}:</strong>
                                                    <span class="int-value">{{relatedKeyword.SearchVolumeScoreAttrs.label}}</span>
                                                    <span class="kw-volume" ng-if="relatedKeyword.SearchVolume > 0">({{relatedKeyword.SearchVolume}})</span>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="interval">
                                                    <i class="fa fa-square {{relatedKeyword.CompetitionScoreAttrs.className}}"></i>
                                                    <strong>{{data.Resources.CompetitionLabel}}:</strong>
                                                    <span class="int-value">{{relatedKeyword.CompetitionScoreAttrs.label}}</span>
                                                    <span class="kw-volume" ng-if="relatedKeyword.Competition > 0">{{displayCompetition(relatedKeyword.Competition)}}</span>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </script>
                                <td class="text-right">
                                    <input type="button" class="suggestion-button"
                                           ng-click="fillKeyword(relatedKeyword)" value="{{data.Resources.SuggestionsLabel}}"/>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </scrollable-table>
                </div>
            </div>
        </div>

        <div style="text-align:center;">
            <table class="wtt-form-table">
                <tbody>
                <tr>
                    <td>
                        <div class="step-no">3. {{data.Resources.SupportingKeywordTitle}}</div>
                    </td>
                    <td>
                        <button popover-placement="auto left" uib-wtt-popover-html="htmlPopoverP" popover-append-to-body="true"
                                popover-trigger="mouseenter" type="button" class="btn-info"><i
                                    class="fa fa-question-circle"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div>
                            <tags-input max-tags=20 enforce-max-tags ng-model="pageKeywordSynonyms" placeholder="Add" replace-spaces-with-dashes="false" class="ti-bootstrap wtt-edit" on-tag-added=onSynonymsAdded($tag) on-tag-removed=onSynonymsRemoved($tag)>
                                <auto-complete source="getKeywordSynonyms($query)" debounce-delay="0" load-on-empty="true" load-on-focus="true" max-results-to-show="30" select-first-match="false"></auto-complete>
                            </tags-input>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>