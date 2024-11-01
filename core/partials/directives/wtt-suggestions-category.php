<div class="suggestions-box" ng-class="{'active': actionConfig.category.isSelected}" id="{{domId}}">
    <script type="text/ng-template" id="suggestion-popover-template.html">
        <div ng-bind-html="tip"></div>
    </script>
    <div class="suggestions-header">
        <button class="btn-collapse" type="button" ng-click="suggestion.isCollapsed = !suggestion.isCollapsed">
            <i class="fa"
               ng-class="{'fa-angle-down': !suggestion.isCollapsed, 'fa-angle-right': suggestion.isCollapsed}"></i>
        </button>
        <h3>{{displayName}}</h3>
        <button type="button" class="btn btn-link btn-xs btn-help btn-help-cq"
                uib-wtt-popover-template="'suggestion-popover-template.html'" popover-append-to-body="true"
                popover-trigger="mouseenter" popover-placement="auto"><i class="fa fa-question-circle"></i></button>
        <div class="material-switch pull-right">
            <input id="{{suggestion.Tag}}" type="checkbox" ng-change="actionConfig.category.toggleSelection()"
                   ng-model="actionConfig.category.isSelected"/>
            <label for="{{suggestion.Tag}}" class="label-success"></label>
        </div>
        <uib-wtt-progressbar animate="true" max="suggestion.displayImportance" value="suggestion.displayScore"
                             type="{{suggestion.progressType}}"
                             ng-if="!actionConfig.category.display"></uib-wtt-progressbar>
        <div class="progress progress-gradient" ng-if="actionConfig.category.display=='gradient'">
            <div class="progress-bar progress-bar-left progress-bar-danger" role="progressbar">
                {{data.Resources[suggestion.Tag + 'LowScale']}}
            </div>
            <div class="progress-bar progress-bar-right progress-bar-danger" role="progressbar">
                {{data.Resources[suggestion.Tag + 'HighScale']}}
            </div>
            <i class="fa fa-caret-up fa-2x progress-indicator" aria-hidden="true"
               ng-style="{'left':suggestion.displayImportance + '%'}"></i>
        </div>
    </div>
    <div class="suggestions-content" uib-collapse="suggestion.isCollapsed">
        <div style="text-align: center;padding-bottom: 10px;" ng-if="actionConfig.buttons.list.length > 0">
            <div class="btn-group" ng-class="{'{{group.Key}}':true}"
                 ng-repeat-start="group in actionConfig.buttons.list">
                <a class="btn btn-xs"
                   ng-class="actionConfig.buttons.isSelected(btn) ? 'wtt-btn-success' : 'btn-default'"
                   ng-click="actionConfig.buttons.select(btn)" ng-repeat="btn in group.Values"><span
                            ng-bind-html="btn.label"></span></a>
            </div>
            <br ng-repeat-end/>
        </div>
        <ul class="suggestions-list">
            <li ng-repeat="rule in suggestion.Rules" class="{{rule.Checked | checkedFilter}}">
                <i class="fa pull-left {{rule.Checked | ruleCheckedFilter}}"></i>
                <div class="table-cell">
                    <script type="text/ng-template" id="suggestion-popover-template-tip.html" ng-if="rule.Tooltip">
                        <div ng-bind-html="rule.Tooltip"></div>
                    </script>
                    <span ng-bind-html="rule.Text" ng-if="rule.Tooltip"
                          uib-wtt-popover-template="'suggestion-popover-template-tip.html'"
                          popover-append-to-body="true" popover-trigger="mouseenter"
                          popover-placement="auto"></span>
                    <span ng-bind-html="rule.Text" ng-if="!rule.Tooltip"></span>
                    <span ng-if="rule._extraInfo && rule._extraInfo.hasDisplayInfo">
                        <a href="#" ng-show="!rule.highlightDisabled || !rule.highlightDisabled !== 'false'" class="btn btn-link btn-xs no-padding"
                           ng-click="$event.preventDefault();highlightContent(rule)"
                           title="{{SlideOutSuggestionsHighlightWordsTooltip}}">
                            <i ng-class="{'fa-eye' : (data2.highlightContentToggle[rule.Text] == null || data2.highlightContentToggle[rule.Text] === true), 'fa-eye-slash' : data2.highlightContentToggle[rule.Text] === false }"
                               class="fa fa-lg" aria-hidden="true"></i>
                        </a>
                        <a href="#" class="btn btn-link btn-xs no-padding"
                           ng-click="$event.preventDefault();viewExtraInfoList(rule)"
                           title="{{SlideOutSuggestionsDisplayListTooltip}}">
                            <i ng-class="{'fa-chevron-down' : (data.viewExtraInfoToggle[rule.Text] == null || data.viewExtraInfoToggle[rule.Text] === true), 'fa-chevron-up' : data.viewExtraInfoToggle[rule.Text] === false }"
                               class="fa fa-lg" aria-hidden="true"></i>
                        </a>
                    </span>
                </div>
                <wtt-page-slideout color="displayColor" info="sliderInfo[rule.Text]" extra-info="rule.ExtraInfo" rule-name="rule.Rule"
                                   content-language-code="contentLanguageCode"
                                   expected-rule-value="rule.Value"
                                   ng-if="data.viewExtraInfoToggle[rule.Text] === false"></wtt-page-slideout>
            </li>
            <li ng-if="!suggestion.Rules || suggestion.Rules.length == 0">
                </i><span ng-bind-html="tip"></span>
            </li>
        </ul>
    </div>
</div>
