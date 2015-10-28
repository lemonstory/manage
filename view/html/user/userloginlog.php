{include file="include/header.html"}
<div class="span9" id="content">
    <div class="row-fluid">
        <!-- block -->
        <div class="block">
            <div class="navbar navbar-inner block-header">
                <div class="muted pull-left">用户登录日志筛选</div>
            </div>
            <div class="block-content collapse in">
                <div class="span12">
                    <form class="form-horizontal" method="post" action="/user/userloginlog.php">
                        <fieldset>
                            <legend>搜索查询</legend>
                            <div class="control-group">
                                <label class="control-label">条件</label>
                                <div class="controls">
                                    <select id="selectError" name="searchCondition">
                                        <option {if $searchCondition=="uid"}selected{/if} value="uid">用户UID</option>
                                        <option {if $searchCondition=="imsi"}selected{/if} value="imsi">设备IMSI编号</option>
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
                    <div class="muted pull-left">登录日志列表</div>
                    <div class="pull-right"><span class="badge badge-info">{$totalCount}</span></div>
                </div>
                <div class="block-content collapse in">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>id</th>
						        <th>uid</th>
						        <th>设备imsi编号</th>
						        <th>时间</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$loglist item=value}
                            <tr>
                                <td>{$value.id}</td>
                                <td>{$value.uid}</td>
                                <td>{$value.imsi}</td>
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