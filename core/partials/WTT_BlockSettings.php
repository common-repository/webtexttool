<div class="wtt_box" ng-show="auth" style="clear: both">
    <div class="wtt_header">{{data.Resources.PageSettingsLabel}}
        <button type="button" class="btn-collapse" ng-click="isCollapsed = !isCollapsed">
            <i class="fa" ng-class="{'fa-plus-square': !isCollapsed, 'fa-minus-square': isCollapsed}"></i>
        </button>
    </div>
    <div class="wtt_blocksettings" ng-show="isCollapsed" style="min-height: 40px; display: block;">
        <div style="padding: 0 10px;">
            <ul class="list-inlines list-unstyleds" style="margin: 15px 5px;">
                <li>
                    <a role="button" class="wtt-dropdown-toggle" data-toggle="wtt-dropdown"
                       aria-haspopup="true"
                       aria-expanded="false"><strong>{{data.Resources.RuleSetLabel}}:</strong> {{selectedDocType.label}}
                        <span class="caret"></span>
                    </a>

                    <ul ng-hide="docTypesEmpty" class="wtt-dropdown-menu dropdown-custom" style="height: auto;max-height: 400px;overflow-x: hidden;">
                        <li ng-repeat="docType in docTypeList"
                            ng-class="{active: docType.label == selectedDocType.label}">
                            <a href="#" ng-click="$event.preventDefault();applyRuleSet(docType)">
                                {{docType.label}}
                            </a>
                        </li>
                    </ul>
                    <ul ng-show="docTypesEmpty">
                        <li><i>{{docTypesError}}</i></li>
                    </ul>
                </li>
                <li>
                    <span style="font-weight: bold; color: #333;">{{data.Resources.ProcessTitleAsH1}}</span>
                    <div class="material-switch pull-right">
                        <input id="process-title-as-h1" type="checkbox"
                               ng-model="checkboxModel.isChecked" ng-change="processTitleAsH1Click()">
                        <label for="process-title-as-h1" class="label-success"></label>
                    </div>
                </li>
                <li>
                    <a role="button" class="wtt-dropdown-toggle" data-toggle="wtt-dropdown"
                       aria-haspopup="true"
                       aria-expanded="false"><strong>{{data.Resources.PageSettingsLanguageLabel}}:</strong> <i class="flag flag-{{activeLanguageCode}}"></i><span
                                class="caret"></span></a>

                    <ul class="wtt-dropdown-menu dropdown-custom">
                        <li ng-repeat="language in languages" ng-class="{active: language.LanguageCode == activeLanguageCode}">
                            <a href="javascript:;" ng-click="setActiveLanguage(language)">
                                <i class="flag flag-{{language.LanguageCode}}"></i>
                                {{language.LanguageName}}
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>