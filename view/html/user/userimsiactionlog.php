{include file="include/header.html"}
<div class="span9" id="content">
    <div class="row-fluid">
        <!-- block -->
        <div class="block">
            <div class="navbar navbar-inner block-header">
                <div class="muted pull-left">用户与设备行为日志筛选</div>
            </div>
            <div class="block-content collapse in">
                <div class="span12">
                    <form class="form-horizontal" method="post" action="/user/userimsiactionlog.php">
                        <fieldset>
                            <legend>搜索查询</legend>
                            <div class="control-group">
                                <label class="control-label">条件</label>
                                <div class="controls">
                                    <select id="selectError" name="searchCondition">
                                        <option {if $searchCondition=="uimid"}selected{/if} value="uimid">用户与设备关联UIMID</option>
                                    </select>
                                    <p class="help-block">请选择查询条件</p>
                                </div>
                            </div>
                            
                            <div class="control-group">
                                <label class="control-label">内容</label>
                                <div class="controls">
                                    <input type="text" class="span6" id="typeahead" name="searchContent" value="{$searchContent}">
                                    <p class="help-block">输入查询内容</p>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">确定</button>
                            </div>
                        </fieldset>
                    </form>

                </div>
            </div>
        </div>
        <!-- /block -->
    </div>
    
    <div class="row-fluid">
        <div>
            <!-- block -->
            <div class="block">
                <div class="navbar navbar-inner block-header">
                    <div class="muted pull-left">日志列表</div>
                    <div class="pull-right"><span class="badge badge-info">{$totalCount}</span></div>
                </div>
                <div class="block-content collapse in">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>id</th>
						        <th>UIMID</th>
						        <th>行为类型</th>
						        <th>行为ID</th>
						        <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$loglist item=value}
                            <tr>
                                <td>{$value.id}</td>
                                <td>{$value.uimid}</td>
                                <td>{$value.actiontype}</td>
                                <td>{$value.actionid}</td>
                                <td>{$value.addtime}</td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /block -->
        </div>
    </div>
    
    {$pageBanner}
</div>


<script>
{literal}
$(function() {
	
});
{/literal}
</script>
{include file="include/footer.html"}