<?php

amd_init_plugin();

amd_admin_head();

$API_OK = amd_api_page_required();
if( !$API_OK )
    return;

/**
 * Tasks per page for task manager
 * @since 1.1.0
 */
$max_in_page = apply_filters( "amd_task_manager_max_in_page", 10 );

?>

<div class="h-20"></div>
<h2 class="margin-0">
    <?php echo get_admin_page_title(); ?>
    <button type="button" class="amd-admin-button _reload_tasks_ --primary --text --sm"><?php esc_html_e( "Reload", "material-dashboard" ); ?></button>
</h2>
<div class="h-10"></div>
<p class="margin-0"><?php echo esc_html_x( "You can manage your website queued and pending tasks to see what is happening in background.", "Admin", "material-dashboard" ); ?></p>
<div class="h-20"></div>

<div class="row">
    <div class="col-lg-11">

        <div class="amd-table" id="tasks-table">
            <table>
                <thead>
                <tr>
                    <th><?php esc_html_e( "ID", "material-dashboard" ); ?></th>
                    <th><?php esc_html_e( "Title", "material-dashboard" ); ?></th>
                    <th><?php echo esc_html_x( "Task user", "Admin tasks", "material-dashboard" ); ?></th>
                    <th><?php echo esc_html_x( "Task repeat", "Admin tasks", "material-dashboard" ); ?></th>
                    <th><?php echo esc_html_x( "Task period", "Admin tasks", "material-dashboard" ); ?></th>
                    <th><?php echo esc_html_x( "Next execution", "Admin tasks", "material-dashboard" ); ?></th>
                    <th><?php echo esc_html_x( "Task actions", "Admin tasks", "material-dashboard" ); ?></th>
                </tr>
                </thead>
                <tbody id="tasks-list">
                </tbody>
            </table>
        </div>

        <div class="text-center" id="tasks-loader">
            <div class="h-10"></div>
            <progress class="hb-progress-circular --loader" style="width:36px;height:36px;font-size:15px"></progress>
            <p class="--text" style="margin:6px 0;font-size:15px"></p>
            <div class="h-10"></div>
        </div>
        <div class="h-10"></div>
        <div class="text-center">
            <button type="button" id="load-more" class="amd-admin-button --sm"><?php esc_html_e( "Load more", "material-dashboard" ); ?></button>
        </div>

    </div>
</div>


<script>
    (function(){
        let $list = $("#tasks-list"), $more = $("#load-more");
        let $table = $("#tasks-table"), $loader = $("#tasks-loader");
        let currentPage = 1;
        let per_page = JSON.parse(`<?php echo wp_json_encode( $max_in_page ); ?>`);

        const taskAction = (task_id, action, onStart=()=>null, onSuccess=()=>null, onError=()=>null) => {
            let n = new AMDNetwork();
            n.clean();
            n.setAction(amd_conf.ajax.private);
            n.put("_ajax_target", "task_manager");
            n.put("task_action", action);
            n.put("task_id", task_id);
            n.on.start = () => onStart()
            n.on.end = (resp, error) => {
                if(!error)
                    resp.success ? onSuccess(resp.data.msg, resp) : onError(resp.data.msg, resp);
                else
                    onError(_t("error"), resp);
            }
            n.post();
        }

        const setLoaderText = text => {
            let $t = $loader.find(".--text");
            let $l = $loader.find(".--loader");
            if(text){
                $t.html(text);
                $l.fadeOut(0);
                $loader.fadeIn(0);
            }
            else{
                $t.html(_t("wait_td"));
                $l.fadeIn(0);
            }
        }

        const setLoader = b => {
            $loader.css("display", b ? "block" : "none");
            $table.setWaiting(b);
            setLoaderText(null);
        }

        const initEvents = () => {
            $("[data-next-execute]").each(function(){
                let $e = $(this);
                if($e.hasClass("--checked"))
                    return;
                let t = parseInt($e.attr("data-next-execute"));
                let id = $e.hasAttr("data-task-id", true);
                if(t && id){
                    let days = Math.floor(t / 86400);
                    if(days >= 30){
                        $e.html(_t("more_than_one_month"));
                    }
                    else if(days >= 7){
                        $e.html(_t("more_than_one_week"));
                    }
                    else if(t >= 1 && t > 86400){
                        $e.html(_n("within_n_days", days).replace("%s", days.toString()));
                    }
                    else {
                        let timer = setInterval(() => {
                            if (t <= 0) {
                                clearInterval(timer);
                                $e.html("-");
                                let $task = $(`[data-task="${id}"]`);
                                taskAction(id, "run", () => $task.setWaiting(), () => $task.removeSlow(), () => $task.setWaiting(false));
                            } else {
                                $e.html($amd.secondsToClock(t));
                            }
                            t--;
                        }, 1000);
                    }
                }
                else{
                    $e.html("-");
                }
            });

            $("[data-task]").each(function(){
                let $e = $(this);
                if($e.hasClass("--checked"))
                    return;
                let id = $e.attr("data-task");
                if(id){
                    let title = $e.find("._title_").html();
                    const finish = $btn => {
                        $e.setWaiting(false);
                        $btn.waitRelease();
                    }
                    const startAction = (action, $btn) => {
                        taskAction(id, action, () => {
                            $btn.waitHold(_t("wait_td"));
                            $e.setWaiting();
                        }, msg => {
                            finish($btn);
                            $e.removeSlow();
                            $amd.toast(msg);
                        }, (msg, resp) => {
                            finish($btn);
                            $amd.toast(msg);
                            if(typeof resp.xhr !== "undefined")
                                console.log(resp.xhr);
                        });
                    }
                    $e.on("click", "._delete_", function(){
                        let $delete = $(this);
                        $amd.alert(_t("delete") + " - " + title, _t("task_delete_confirm"), {
                            confirmButton: _t("yes"),
                            cancelButton: _t("no"),
                            onConfirm: () => startAction("delete", $delete)
                        });
                    });
                    $e.on("click", "._run_", function(){
                        let $run = $(this);
                        $amd.alert(_t("run") + " - " + title, _t("task_run_confirm"), {
                            confirmButton: _t("yes"),
                            cancelButton: _t("no"),
                            onConfirm: () => startAction("run", $run)
                        });
                    });
                }
            });
        }

        $more.fadeOut(0);

        const reloadTasks = () => {

            if(currentPage === 1)
                $list.html("");

            let n = new AMDNetwork();
            n.clean();
            n.setAction(amd_conf.ajax.private);
            n.put("_ajax_target", "task_manager");
            n.put("get_tasks", "");
            n.put("current_page", currentPage);
            n.put("per_page", per_page);
            n.on.start = () => setLoader(1);
            n.on.end = (resp, error) => {
                setLoader(0);
                if(!error){
                    if(resp.success){
                        let tasks = Object.entries(resp.data.tasks || {});
                        let hasMore = resp.data.has_more || false;
                        if(hasMore) $more.fadeIn();
                        else $more.fadeOut(0);
                        if(tasks && tasks.length){
                            let html = "";
                            for(let [k, data] of tasks){
                                let {id, title, user_id, user_fullname, repeats, period, period_str, is_visible, is_deletable, is_executable, execution_time} = data;
                                if(is_visible){
                                    html += `<tr data-task="${id}">
                    <td>${id}</td>
                    <td><span class="_title_">${title}</span></td>
                    <td>
                        ${user_id > 0 ? `#${user_id}<br><span class="tiny-text color-primary">${user_fullname}</span>` : `-`}
                    </td>
                    <td>${_t("task_n_times").replace("%s", repeats)}</td>
                    <td>${period_str}</td>
                    <td data-next-execute="${execution_time}" data-task-id="${id}">
                        <progress class="hb-progress-circular" style="width:20px;height:20px;font-size:13px"></progress>
                    </td>
                    <td>
                        ${is_executable ? `<button type="button" class="amd-admin-button _run_ --primary --text --sm">${_t("run")}</button>` : ``}
                        ${is_deletable ? `<button type="button" class="amd-admin-button _delete_ --red --text --sm">${_t("delete")}</button>` : ``}
                    </td>
                </tr>`;
                                }
                            }
                            $list.append(html);
                            initEvents();
                        }
                        else{
                            setLoaderText(_t("no_results"));
                        }
                    }
                    else{
                        setLoaderText(resp.data.msg);
                    }
                }
                else{
                    setLoaderText(_t("error"));
                    console.log(resp.xhr);
                }
            }
            n.post();

        }
        reloadTasks();

        $("._reload_tasks_").click(function(){
            currentPage = 1;
            reloadTasks();
        });

        $more.click(function(){
            currentPage++;
            reloadTasks();
        });
    }());
</script>