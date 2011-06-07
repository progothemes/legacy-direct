<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package ProGo
 * @subpackage Direct
 * @since Direct 1.0
 */

get_header(); ?>
        <div id="container" class="container_12">
			<div id="main" class="grid_8">

			<div id="post-0" class="post error404 not-found">
				<h1 class="page-title"><?php _e( 'Not Found', 'twentyten' ); ?></h1>
				<div class="grid_11 entry">
					<p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'twentyten' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .entry -->
			</div><!-- #post-0 -->

		</div><!-- #main -->
	</div><!-- #container -->
	<script type="text/javascript">
		// focus on search field after it has loaded
		document.getElementById('s') && document.getElementById('s').focus();
	</script>

<?php get_footer(); ?>