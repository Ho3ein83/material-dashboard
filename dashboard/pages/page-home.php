<?php

$icon_cards = apply_filters( "amd_get_dashboard_cards", "icon_card" );
if( empty( $icon_cards ) OR !is_array( $icon_cards ) )
	$icon_cards = [];

$cards = apply_filters( "amd_get_dashboard_cards", "content_card,title_card" );
if( empty( $cards ) OR !is_array( $cards ) )
	$cards = [];

$row_cards = apply_filters( "amd_get_dashboard_cards", "home_col_card" );
if( empty( $row_cards ) OR !is_array( $row_cards ) )
	$row_cards = [];

# @formatter off
?>

<?php
    /**
     * Before home page icon cards
     * @since 1.0.0
     */
    do_action( "amd_home_before_icon_cards" );
?>

<div class="row">
    <?php foreach( $icon_cards as $id => $card ): ?>
        <div class="col-lg-3" id="<?php echo esc_attr( "icon-card-" . ( $card["_id"] ?? $id ) ); ?>">
            <?php amd_dump_single_card( array(
                "type" => "icon_card",
                "title" => $card["title"] ?? "",
                "icon" => $card["icon"] ?? "",
                "color" => $card["color"] ?? "",
                "subtext" => $card["subtext"] ?? "",
                "content" => $card["text"] ?? "",
                "footer" => $card["footer"] ?? ""
            ) ); ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="h-10"></div>

<?php
    /**
     * After home page icon cards
     * @since 1.0.1
     */
    do_action( "amd_home_after_icon_cards" );
?>

<?php
    /**
     * Before home page cards
     * @since 1.0.0
     */
    do_action( "amd_home_before_cards" );
?>

<div class="card-columns template-1">
    <?php
        /**
         * Before home page cards column
         * @since 1.0.1
         */
        do_action( "amd_home_cards_column_start" );

        foreach( $cards as $card )
            amd_dump_single_card( $card );

        /**
         * After home page cards column
         * @since 1.0.1
         */
        do_action( "amd_home_cards_column_end" );
    ?>
</div>

<div class="h-10"></div>

<div class="row">
    <?php
        /**
         * Home page before cards row
         * @since 1.0.5
         */
        do_action( "amd_home_before_cards_row" );

        foreach( $row_cards as $row_card ){
            $row_card["type"] = "col_card";
            amd_dump_single_card( $row_card );
        }

        /**
         * Home page after cards row
         * @since 1.0.5
         */
        do_action( "amd_home_after_cards_row" );
    ?>
</div>

<?php
    /**
     * After home page cards
     * @since 1.0.0
     */
    do_action( "amd_home_after_cards" );
?>

<?php amd_front_spacer( 100 ); ?>

<?php # @formatter on ?>
