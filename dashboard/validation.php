<?php

/**
 * Validation fields data
 * @since 1.2.0
 */
$validate_fields = apply_filters( "amd_account_validation_fields", [], amd_get_current_user() );

?>

<div class="text-center">
    <div class="step-indicators">
        <?php foreach( $validate_fields as $id => $data ): ?>
        <div class="--step" data-step-indicator="<?php echo esc_attr( $id ); ?>"><?php _amd_icon( $data["icon"] ?? "" ); ?></div>
        <?php endforeach; ?>
        <div class="--step --complete" data-step-indicator="finish"><?php _amd_icon( "check" ); ?></div>
    </div>
    <div class="h-10"></div>
    <p class="margin-0 font-title small-text"><?php esc_html_e( "You are a few steps behind setting up your account", "material-dashboard" ); ?></p>
    <div class="steps amd-card">

        <?php $counter = 0; ?>
        <?php foreach( $validate_fields as $id => $data ): ?>
            <?php
            $id = $data["id"] ?? "";
            if( empty( $id ) )
                continue;
            $counter++;
            ?>
            <div class="--item" data-step="<?php echo esc_attr( $id ); ?>" data-step-index="<?php echo esc_attr( $counter ); ?>">
                <?php do_action( "amd_account_validation_field_$id", $data ); ?>
            </div>
        <?php endforeach; ?>

        <div class="--item" data-step="finish" data-step-index="<?php echo esc_attr( $counter+1 ); ?>">
            <h3><?php esc_html_e( "All done!", "material-dashboard" ); ?></h3>
            <p><?php esc_html_e( "Thanks for your time, you can now access your dashboard", "material-dashboard" ); ?></p>
        </div>

        <div class="h-10"></div>
        <div class="flex-align flex-center">
            <button type="button" class="btn" id="next-btn" style="width:100%;height:50px;font-size:16px"><?php esc_html_e( "Next", "material-dashboard" ); ?></button>
            <a href="<?php echo esc_url( amd_get_dashboard_url() ); ?>" class="btn" id="back-btn" style="width:100%;height:50px;font-size:16px"><?php esc_html_e( "Back to dashboard", "material-dashboard" ); ?></a>
        </div>
    </div>
</div>
<script>
    (() => {
        const $next_btn = $("#next-btn"), $back_btn = $("#back-btn");

        const get_step = () => {
            return $("[data-step].active").first().attr("data-step");
        }
        const get_step_index = () => {
            return parseInt($(`[data-step="${get_step()}"]`).attr("data-step-index"));
        }
        const get_next_step = () => {
            return $(`[data-step-index=${get_step_index()+1}]`).attr("data-step");
        }
        const set_step = s => {
            if(!s) return;
            $next_btn[s === "finish" ? "fadeOut" : "fadeIn"](0);
            $back_btn[s === "finish" ? "fadeIn" : "fadeOut"](0);
            $("[data-step]").fadeOut(0).removeClass("active");
            $(`[data-step="${s}"]`).fadeIn(300, function(){
                $(this).addClass("active");
            });
            $(`[data-step-indicator="${s}"]`).addClass("active");
        }
        set_step($("[data-step]:first-child").attr("data-step"));

        $next_btn.click(() => {
            const s = get_step();
            if(typeof window[`_validate_step_${s}`] === "function"){
                $next_btn.waitHold(_t("wait_td"));
                const complete = () => {
                    console.log(get_next_step());
                    set_step(get_next_step());
                    $next_btn.waitRelease();
                }
                const fail = () => {
                    $next_btn.waitRelease();
                }
                const v = window[`_validate_step_${s}`](complete, fail);
            }
        });
    })();
</script>
<style>
    .step-indicators > .--step,
    .step-indicators {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .step-indicators {
        flex-wrap: wrap;
        gap: 16px;
    }
    .step-indicators > .--step {
        aspect-ratio: 1;
        width: 40px;
        background: var(--amd-primary-x-low);
        padding: 8px;
        border-radius: 50%;
        transition: all ease .3s;
    }
    .step-indicators > .--step.active {
        background: var(--amd-primary);
        color: #fff;
    }
    .step-indicators > .--step.--complete.active {
        background: var(--amd-color-green);
    }
    .steps {
        max-width: 500px;
        margin: 16px auto;
    }
    .steps > .--item:not(.active) {
        display: none;
    }
</style>
