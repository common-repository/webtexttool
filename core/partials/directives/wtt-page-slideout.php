<div id="slideout">
    <script type="text/ng-template" id="popoverTemplate.html">
        <div ng-bind-html="item.tip"></div>
    </script>
    <div ng-if="info.Type=='info'" class="scrollable">
        <table>
            <tr ng-if="info.Description">
                <td colspan="2" class="sub-title">
                    <span ng-bind-html="info.Description"></span>
                </td>
            </tr>
            <tr>
                <td class="tags" colspan="2">

                    <span ng-if="hasMenu(ruleName) == false" ng-repeat="item in info.tags track by $index">
                        <span ng-if="item.tip.length > 0" class="post-tag cursor-default"
                              ng-click="markText(item)" uib-wtt-popover-template="'tag-info-template.html'" popover-title="{{ item.message || data.Resources.SlideOutSuggestionsTooltipHeader }}" popover-trigger="mouseenter" ng-class="item.type"
                              popover-append-to-body="true" popover-placement="auto" ng-style="getColor(color)">
                                {{item.word}}
                            <span class="badge" ng-show="item.count > 1"> {{item.count}}</span><i ng-show="item.dataSource" class="fa fa-lg fa-lightbulb-o tag-lightbulb"></i>
                            <script type="text/ng-template" id="tag-info-template.html">
                                <div id="slideout" ng-bind-html="item.tip" class="suggestion-tip"></div>
                            </script>
                        </span>
                        <span class="post-tag" ng-if="item.tip.length == 0" ng-click="markText(item)" ng-class="item.type" ng-style="getColor(color)">
                            {{item.word}}
                            <span class="badge" ng-show="item.count > 1"> {{item.count}}</span>
                            <i ng-show="item.dataSource" class="fa fa-lg fa-lightbulb-o tag-lightbulb"></i>
                        </span>
                    </span>
                    <span ng-repeat="item in info.tags track by $index">
                        <a ng-if="hasMenu(ruleName)" class="post-tag wtt-dropdown-toggle" ng-class="item.type"
                           wtt-context="context_{{ruleName}}_{{$index}}" wtt-rule-name="{{ruleName}}" data-toggle="wtt-dropdown" role="button">
                            {{item.word}}
                            <span class="badge" ng-show="item.count > 1"> {{item.count}}</span><i ng-show="item.dataSource" class="fa fa-lg fa-lightbulb-o tag-lightbulb"></i>
                        </a>

                        <ul id="context_{{ruleName}}_{{$index}}" class="wtt-dropdown-menu wtt-context-menu">
                            <li ng-show="item.dataSource" ng-repeat="suggestion in item.dataSource" ng-class="suggestion.type" ng-click="hideMenu($event, ruleName, $parent.$index);replaceWithSuggestion(item, suggestion)">{{suggestion.word}}</li>
                            <li ng-if="hasFeature('EnhanceText') && hasPrompt(extraInfo)" class="content-generator" ng-click="hideMenu($event, ruleName, $index);contentGenerator(item, info);">{{EditorHighlightTooltipAssistant}}</li>
                            <li class="highlight-word" ng-click="hideMenu($event, ruleName, $index);markText(item);">{{EditorHighlightTooltipHighligthWord}}</li>
                        </ul>
                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>