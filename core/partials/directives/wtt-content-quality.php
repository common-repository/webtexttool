<div class="suggestions-box">
    <div class="suggestions-content">
        <div class="dropup">
            <button type="button" class="wtt-btn2 wtt-btn-info wtt-btn-block" title="{{data.Resources.QualityAnalyzeContentLabel}}" ladda="analyzing" ng-click="analyze()"><i class="fa fa-rocket" aria-hidden="true"></i>
                {{data.Resources.QualityAnalyzeContentLabel}}
            </button>
        </div>
    </div>
</div>

<script type="text/ng-template" id="suggestion-popover-template2.html">
    <div>{{data.Resources.ReadingLevelHelp}}</div>
</script>
<script type="text/ng-template" id="suggestion-popover-template1.html">
    <div ng-bind-html="showReadingLevelHelp"></div>
</script>

<div class="suggestions-mask cq">
    <div class="suggestions">
        <div class="suggestions-box white no-border" ng-show="displayVVRecruitment" style="padding-bottom: 20px;padding-top: 20px;">
            <div class="suggestions-header">
                <button class="btn-collapse" type="button" ng-click="isRecruitmentRulesCollapsed = !isRecruitmentRulesCollapsed">
                    <i class="fa" ng-class="{'fa-angle-down': !isRecruitmentRulesCollapsed, 'fa-angle-right': isRecruitmentRulesCollapsed}"></i>
                </button>
                <h3>{{data.Resources.RecruitmentRulesTitle}}</h3>
                <div class="material-switch pull-right">
                    <a id="btnSuggestJob" type="button" ng-click="suggestJob()" title="{{data.Resources.RecruitmentRulesSettingsButton}}" class="btn btn-xs btn-default">Settings</a>
                </div>
                <uib-wtt-progressbar animate="true" max="10" value="10" type="success"></uib-wtt-progressbar>
            </div>
            <div class="suggestions-content" uib-collapse="isRecruitmentRulesCollapsed">
                <script type="text/ng-template" id="recruitment-feasebility-template.html">
                    <div ng-bind-html="data.Resources.RecruitmentCategTitleRecruitmentFeasebilityHelpTooltip"></div>
                </script>
                <script type="text/ng-template" id="recruitment-feasebility-descr.html">
                    <div ng-bind-html="recruitmentFeasebility"></div>
                </script>
                <ul class="suggestions-list">
                    <li>{{data.Resources.RecruitmentCategTitleLabel}}<span class="pull-right">{{data2.ActivePage.pageSettings.JobTitleData.Title}}{{data2.ActivePage.pageSettings.JobTitleData.Level ? (data2.ActivePage.pageSettings.JobTitleData.Title ? " / " : "" ) + data2.ActivePage.pageSettings.JobTitleData.Level : "" }}</span></li>
                    <li  ng-if="debugMode">{{data.Resources.RecruitmentCategLocationLabel}}<span class="pull-right">{{data2.ActivePage.pageSettings.JobTitleData.Province}} {{data2.ActivePage.pageSettings.JobTitleData.KeywordSource.Country ? (data2.ActivePage.pageSettings.JobTitleData.Province ? " / " : "") + data2.ActivePage.pageSettings.JobTitleData.KeywordSource.Country : ""}}</span></li>
                    <li ng-if="allowedRecruitmentISCO">{{data.Resources.RecruitmentCategISCOLabel}}<span class="pull-right">{{ISCOCode}}</span></li>
                    <li class="recruitment">
                        {{data.Resources.RecruitmentCategTitleRecruitmentFeasebilityLabel}}
                        <a href="javascript:;" class="btn btn-link btn-xs btn-help btn-help-cq" uib-wtt-popover-template="'recruitment-feasebility-template.html'" popover-append-to-body="true" popover-trigger="mouseenter" popover-placement="bottom"><i class="fa fa-question-circle"></i></a>

                        <span class="pull-right" ng-class="recruitmentFeasebilityCssClass">
                            <i class="fa feasebility-icon" ng-class="recruitmentFeasebilityIcon" uib-wtt-popover-template="'recruitment-feasebility-descr.html'" popover-append-to-body="true" popover-trigger="mouseenter" popover-placement="auto"></i>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="suggestions-box" ng-show="contentQualityDetails && !hideTextStatistics">
            <div class="suggestions-header">
                <button class="btn-collapse" type="button" ng-click="isSuggestionDetailsCollapsed = !isSuggestionDetailsCollapsed">
                    <i class="fa" ng-class="{'fa-angle-down': !isSuggestionDetailsCollapsed, 'fa-angle-right': isSuggestionDetailsCollapsed}"></i>
                </button>
                <h3>{{data.Resources.ContentDetailsLabel}}</h3>
                <uib-wtt-progressbar animate="true" max="10" value="10" type="success"></uib-wtt-progressbar>
            </div>

            <div class="suggestions-content" uib-collapse="isSuggestionDetailsCollapsed">
                <ul class="suggestions-list">
                    <li>{{data.Resources.ReadingTimeLabel}}:<span class="pull-right">{{contentQualityDetails.ReadingTime}}</span></li>
                    <li>{{data.Resources.ReadingLevelLabel}}: <span class="pull-right">{{contentQualityDetails.ReadingLevel}}<button ng-hide="contentQualityDetails.ReadingValues" class="btn btn-link btn-xs btn-help btn-help-cq" type="button" style="position: relative; top: -2px;"
                                                                                                              uib-wtt-popover-template="'suggestion-popover-template1.html'" popover-append-to-body="true"
                                                                                                              popover-trigger="mouseenter" popover-placement="auto top"><i class="fa fa-question-circle"></i></button></span>
                </ul>
            </div>
        </div>

        <div class="suggestions-box active white no-border" ng-show="contentQualityDetails && docTypeCustomSettings.ComplexityData && docTypeCustomSettings.ComplexityData.Title">
            <div class="suggestions-header">
                <button class="btn-collapse" type="button" ng-click="isDocTypeComplexityDataCollapsed = !isDocTypeComplexityDataCollapsed">
                    <i class="fa" ng-class="{'fa-angle-down': !isDocTypeComplexityDataCollapsed, 'fa-angle-right': isDocTypeComplexityDataCollapsed}"></i>
                </button>
                <h3>{{docTypeCustomSettings.ComplexityData.Title}}</h3>
                <uib-wtt-progressbar animate="true" max="10" value="10" type="success"></uib-wtt-progressbar>
            </div>
            <div class="suggestions-content" uib-collapse="isDocTypeComplexityDataCollapsed">
                <ul class="suggestions-list">
                    <li>{{docTypeCustomSettings.ComplexityData.Value}}</li>
                </ul>
            </div>
        </div>

        <div class="suggestions-box" ng-show="contentQualityDetails.ReadingValues">

            <div class="suggestions-header">
                <button class="btn-collapse" type="button"
                        ng-click="isReadingLevelDetailsCollapsed = !isReadingLevelDetailsCollapsed">
                    <i class="fa"
                       ng-class="{'fa-angle-down': !isReadingLevelDetailsCollapsed, 'fa-angle-right': isReadingLevelDetailsCollapsed}"></i>
                </button>
                <h3>Reading Level Details</h3>
                <button class="btn btn-link btn-xs btn-help btn-help-cq" type="button" title=""
                        uib-wtt-popover-template="'suggestion-popover-template2.html'" popover-append-to-body="true"
                        popover-trigger="mouseenter" popover-placement="auto top"><i class="fa fa-question-circle"></i></button>
            </div>
            <div class="suggestions-content" uib-collapse="isReadingLevelDetailsCollapsed">
                <table style="width: 100%; font-size: 11.5px;">
                    <tbody>
                    <tr>
                        <td><a href="https://en.wikipedia.org/wiki/Coleman%E2%80%93Liau_index" target="_blank">Coleman Liau
                            Index</a></td>
                        <td> {{contentQualityDetails.ReadingValues.ColemanLiauIndex | number : 1}}</td>
                    </tr>
                    <tr>
                        <td><a href="https://en.wikipedia.org/wiki/Automated_readability_index" target="_blank">Automated
                            Readability Index</a></td>
                        <td> {{contentQualityDetails.ReadingValues.AutomatedReadabilityIndex | number : 1}}</td>
                    </tr>
                    <tr>
                        <td><a href="https://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests"
                               target="_blank">Flesch-Kincaid Reading ease</a></td>
                        <td> {{contentQualityDetails.ReadingValues.FleschKincaidReadingEasy | number : 1}}</td>
                    </tr>
                    <tr>
                        <td> Average</td>
                        <td> {{contentQualityDetails.ReadingValues.ReadingAvg | number : 1}}
                            ({{contentQualityDetails.ReadingLevel}})
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="suggestions-box" ng-hide="contentQualityDetails">
            <div class="suggestions-content" uib-collapse="isSuggestionDetailsCollapsed">
                <ul class="suggestions-list">
                    <li ng-bind-html="data.Resources.CQSuggestionsIntro"></li>
                </ul>
            </div>
        </div>
        <wtt-suggestions-category ng-repeat="suggestion in contentQualitySuggestions" suggestion="suggestion"
                                  content-language-code="contentLanguageCode" type="cq" version="contentQualityVersion"></wtt-suggestions-category>
    </div>
</div>