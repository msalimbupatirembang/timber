<?php

	class TestTimberPost extends Timber_UnitTestCase {

		function testPostObject(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$this->assertEquals('TimberPost', get_class($post));
			$this->assertEquals($post_id, $post->ID);
		}

		function testNameMethod() {
			$post_id = $this->factory->post->create(array('post_title' => 'Battlestar Galactica'));
			$post = new TimberPost($post_id);
			$this->assertEquals('Battlestar Galactica', $post->name());
		}

		function testGetImage() {
			$post_id = $this->factory->post->create(array('post_title' => 'St. Louis History'));
			$filename = TestTimberImage::copyTestImage( 'arch.jpg' );
			$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
			$iid = wp_insert_attachment( $attachment, $filename, $post_id );
			update_post_meta($post_id, 'landmark', $iid);
			$post = new TimberPost($post_id);
			$image = $post->get_image('landmark');
			$this->assertEquals('The Arch', $image->title());
		}

		function testPostString() {
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$post = new TimberPost($post_id);
			$str = Timber::compile_string('<h1>{{post}}</h1>', array('post' => $post));
			$this->assertEquals('<h1>Gobbles</h1>', $str);
		}

		function testFalseParent() {
			$pid = $this->factory->post->create();
			$filename = TestTimberImage::copyTestImage( 'arch.jpg' );
			$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
			$iid = wp_insert_attachment( $attachment, $filename, $pid );
			update_post_meta( $iid, 'architect', 'Eero Saarinen' );
			$image = new TimberImage( $iid );
			$parent = $image->parent();
			$this->assertEquals($pid, $parent->ID);
			$this->assertFalse($parent->parent());
		}

		function testPostOnSingle(){
			$post_id = $this->factory->post->create();
			$this->go_to(home_url('/?p='.$post_id));
			$post = new TimberPost();
			$this->assertEquals($post_id, $post->ID);
		}

		function testPostOnSingleQuery(){
			$post_id = $this->factory->post->create();
			$this->go_to(home_url('/?p='.$post_id));
			$post_id = $this->factory->post->create();
			$post = Timber::query_post($post_id);
			$this->assertEquals($post_id, $post->ID);
			$this->assertEquals($post_id, get_the_ID());
		}

		function testPostOnSingleQueryNoParams(){
			$post_id = $this->factory->post->create();
			$this->go_to(home_url('/?p='.$post_id));
			$post = Timber::query_post();
			$this->assertEquals($post_id, $post->ID);
			$this->assertEquals($post_id, get_the_ID());
		}

		// function testPostOnBuddyPressPage(){
		// 	$post_id = $this->factory->post->create();
		// 	global $post;
		// 	$this->go_to(home_url('/?p='.$post_id));
		// 	$_post = $post;
		// 	$post = false;
		// 	$my_post = new TimberPost();
		// 	$this->assertEquals($post_id, $my_post->ID);
		// }

		function testNonexistentProperty(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost( $post_id );
			$this->assertFalse( $post->zebra );
		}

		function testNonexistentMethod(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost( $post_id );
			$this->assertFalse( $post->donkey() );
		}

		function testNext(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$this->assertEquals($firstPost->next()->ID, $nextPost->ID);
		}

		function testNextCategory(){
			$posts = array();
			for($i = 0; $i<4; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			wp_set_object_terms($posts[0], 'TestMe', 'category', false);
			wp_set_object_terms($posts[2], 'TestMe', 'category', false);
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[2]);
			$this->assertEquals($firstPost->next('category')->ID, $nextPost->ID);
		}

		function testNextCustomTax(){
			$v = get_bloginfo('version');
			if (version_compare($v, '3.8', '<')) {
           		$this->markTestSkipped('Custom taxonomy prev/next not supported until 3.8');
        	} else {
				register_taxonomy('pizza', 'post');
				$posts = array();
				for($i = 0; $i<4; $i++){
					$j = $i + 1;
					$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
				}
				wp_set_object_terms($posts[0], 'Cheese', 'pizza', false);
				wp_set_object_terms($posts[2], 'Cheese', 'pizza', false);
				wp_set_object_terms($posts[3], 'Mushroom', 'pizza', false);
				$firstPost = new TimberPost($posts[0]);
				$nextPost = new TimberPost($posts[2]);
				$this->assertEquals($firstPost->next('pizza')->ID, $nextPost->ID);
			}
		}

		function testPrev(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$lastPost = new TimberPost($posts[1]);
			$prevPost = new TimberPost($posts[0]);
			$this->assertEquals($lastPost->prev()->ID, $prevPost->ID);
		}

		function testPrevCustomTax(){
			$v = get_bloginfo('version');
			if (version_compare($v, '3.8', '<')) {
           		$this->markTestSkipped('Custom taxonomy prev/next not supported until 3.8');
        	} else {
				register_taxonomy('pizza', 'post');
				$posts = array();
				for($i = 0; $i<3; $i++){
					$j = $i + 1;
					$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
				}
				wp_set_object_terms($posts[0], 'Cheese', 'pizza', false);
				wp_set_object_terms($posts[2], 'Cheese', 'pizza', false);
				$lastPost = new TimberPost($posts[2]);
				$prevPost = new TimberPost($posts[0]);
				$this->assertEquals($lastPost->prev('pizza')->ID, $prevPost->ID);
			}
		}

		function testPrevCategory(){
			$posts = array();
			for($i = 0; $i<3; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			wp_set_object_terms($posts[0], 'TestMe', 'category', false);
			wp_set_object_terms($posts[2], 'TestMe', 'category', false);
			$lastPost = new TimberPost($posts[2]);
			$prevPost = new TimberPost($posts[0]);
			$this->assertEquals($lastPost->prev('category')->ID, $prevPost->ID);
		}

		function testNextWithDraftAndFallover(){
			$posts = array();
			for($i = 0; $i<3; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$nextPostAfter = new TimberPost($posts[2]);
			$nextPost->post_status = 'draft';
			wp_update_post($nextPost);
			$this->assertEquals($firstPost->next()->ID, $nextPostAfter->ID);
		}

		function testNextWithDraft(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$nextPost->post_status = 'draft';
			wp_update_post($nextPost);
			$nextPostTest = $firstPost->next();
		}

		function testPostInitObject(){
			$post_id = $this->factory->post->create();
			$post = get_post($post_id);
			$post = new TimberPost($post);
			$this->assertEquals($post->ID, $post_id);
		}

		function testPostByName(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$pid_from_name = TimberPost::get_post_id_by_name($post->post_name);
			$this->assertEquals($pid_from_name, $post_id);
		}

		function testUpdate(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$rand = rand_str();
			$post->update('test_meta', $rand);
			$post = new TimberPost($post_id);
			$this->assertEquals($rand, $post->test_meta);
		}

		function testCanEdit(){
			wp_set_current_user(1);
			$post_id = $this->factory->post->create(array('post_author' => 1));
			$post = new TimberPost($post_id);
			$this->assertTrue($post->can_edit());
			wp_set_current_user(0);
		}



		function testTitle(){
			$title = 'Fifteen Million Merits';
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_title = $title;
			wp_update_post($post);
			$this->assertEquals($title, trim(strip_tags($post->title())));
			$this->assertEquals($title, trim(strip_tags($post->get_title())));
		}

		function testPreviewContent(){
			$quote = 'The way to do well is to do well.';
			$post_id = $this->factory->post->create(array(
				'post_content' => $quote
			));
			$revision_id = $this->factory->post->create(array(
				'post_type' => 'revision',
				'post_parent' => $post_id,
				'post_content' => $quote . 'Yes'
			));

			$_GET['preview'] = true;
			$_GET['preview_nonce'] = wp_create_nonce('post_preview_' . $post_id);
			$post = new TimberPost($post_id);
			$this->assertEquals($post->post_content, $quote . 'Yes');
		}

		function testContent(){
			$quote = 'The way to do well is to do well.';
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_content = $quote;
			wp_update_post($post);
			$this->assertEquals($quote, trim(strip_tags($post->content())));
			$this->assertEquals($quote, trim(strip_tags($post->get_content())));
		}

		function testContentPaged(){
            $quote = $page1 = 'The way to do well is to do well.';
            $quote .= '<!--nextpage-->';
            $quote .= $page2 = "And do not let your tongue get ahead of your mind.";

            $post_id = $this->factory->post->create();
            $post = new TimberPost($post_id);
            $post->post_content = $quote;
            wp_update_post($post);

            $this->assertEquals($page1, trim(strip_tags($post->content(1))));
            $this->assertEquals($page2, trim(strip_tags($post->content(2))));
            $this->assertEquals($page1, trim(strip_tags($post->get_content(0,1))));
            $this->assertEquals($page2, trim(strip_tags($post->get_content(0,2))));
		}

        function testPagedContent(){
            $quote = $page1 = 'Named must your fear be before banish it you can.';
            $quote .= '<!--nextpage-->';
            $quote .= $page2 = "No, try not. Do or do not. There is no try.";

            $post_id = $this->factory->post->create(array('post_content' => $quote));

            $this->go_to( get_permalink( $post_id ) );

            // @todo The below should work magically when the iterators are merged
            setup_postdata( get_post( $post_id ) );

            $post = Timber::get_post();
			$this->assertEquals($page1, trim(strip_tags( $post->paged_content() )));

            $pagination = $post->pagination();
            $this->go_to( $pagination['pages'][1]['link'] );

            setup_postdata( get_post( $post_id ) );
            $post = Timber::get_post();

			$this->assertEquals($page2, trim(strip_tags( $post->get_paged_content() )));
		}

		function testMetaCustomArrayFilter(){
			add_filter('timber_post_get_meta', function($customs){
				foreach($customs as $key=>$value){
					$flat_key = str_replace('-', '_', $key);
					$flat_key .= '_flat';
					$customs[$flat_key] = $value;
				}
				// print_r($customs);
				return $customs;
			});
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'the-field-name', 'the-value');
			update_post_meta($post_id, 'with_underscores', 'the_value');
			$post = new TimberPost($post_id);
			$this->assertEquals($post->with_underscores_flat, 'the_value');
			$this->assertEquals($post->the_field_name_flat, 'the-value');
		}

		function testPostMetaMetaException(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$string = Timber::compile_string('My {{post.meta}}', array('post' => $post));
			$this->assertEquals('My', trim($string));
			update_post_meta($post_id, 'meta', 'steak');
			$post = new TimberPost($post_id);
			$string = Timber::compile_string('My {{post.custom.meta}}', array('post' => $post));
			//sorry you can't over-write methods now
			$this->assertEquals('My steak', trim($string));
		}

		function testPostParent(){
			$parent_id = $this->factory->post->create();
			$child_id = $this->factory->post->create(array('post_parent' => $parent_id));
			$child_post = new TimberPost($child_id);
			$this->assertEquals($parent_id, $child_post->parent()->ID);
		}

		function testPostSlug(){
			$pid = $this->factory->post->create(array('post_name' => 'the-adventures-of-tom-sawyer'));
			$post = new TimberPost($pid);
			$this->assertEquals('the-adventures-of-tom-sawyer', $post->slug);
		}

		function testPostAuthor(){
			$author_id = $this->factory->user->create(array('display_name' => 'Jared Novack', 'user_login' => 'jared-novack'));
			$pid = $this->factory->post->create(array('post_author' => $author_id));
			$post = new TimberPost($pid);
			$this->assertEquals('jared-novack', $post->author()->slug());
			$this->assertEquals('Jared Novack', $post->author()->name());
			$template = 'By {{post.author}}';
			$authorCompile = Timber::compile_string($template, array('post' => $post));
			$template = 'By {{post.author.name}}';
			$authorNameCompile = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals($authorCompile, $authorNameCompile);
			$this->assertEquals('By Jared Novack', $authorCompile);
		}

		function testPostAuthorInTwig(){
			$author_id = $this->factory->user->create(array('display_name' => 'Jon Stewart', 'user_login' => 'jon-stewart'));
			$pid = $this->factory->post->create(array('post_author' => $author_id));
			$post = new TimberPost($pid);
			$this->assertEquals('jon-stewart', $post->author()->slug());
			$this->assertEquals('Jon Stewart', $post->author()->name());
			$template = 'By {{post.author}}';
			$authorCompile = Timber::compile_string($template, array('post' => $post));
			$template = 'By {{post.author.name}}';
			$authorNameCompile = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals($authorCompile, $authorNameCompile);
			$this->assertEquals('By Jon Stewart', $authorCompile);
		}

		function testPostModifiedAuthor() {
			$author_id = $this->factory->user->create(array('display_name' => 'Woodward', 'user_login' => 'bob-woodward'));
			$mod_author_id = $this->factory->user->create(array('display_name' => 'Bernstein', 'user_login' => 'carl-bernstein'));
			$pid = $this->factory->post->create(array('post_author' => $author_id));
			$post = new TimberPost($pid);
			$this->assertEquals('bob-woodward', $post->author()->slug());
			$this->assertEquals('bob-woodward', $post->modified_author()->slug());
			$this->assertEquals('Woodward', $post->author()->name());
			$this->assertEquals('Woodward', $post->modified_author()->name());
			update_post_meta($pid, '_edit_last', $mod_author_id);
			$this->assertEquals('bob-woodward', $post->author()->slug());
			$this->assertEquals('carl-bernstein', $post->modified_author()->slug());
			$this->assertEquals('Woodward', $post->author()->name());
			$this->assertEquals('Bernstein', $post->modified_author()->name());
		}

		function tearDown() {
			global $wpdb;
			$query = "DELETE from $wpdb->users WHERE ID > 1";
			$wpdb->query($query);
		}

		function testPostFormat() {
			add_theme_support( 'post-formats', array( 'aside', 'gallery' ) );
			$pid = $this->factory->post->create();
			set_post_format($pid, 'aside');
			$post = new TimberPost($pid);
			$this->assertEquals('aside', $post->format());
		}

		function testPostClass(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$this->assertEquals('post-'.$pid.' post type-post status-publish format-standard hentry category-uncategorized', $post->class);
		}

		function testPostChildren(){
			$parent_id = $this->factory->post->create();
			$children = $this->factory->post->create_many(8, array('post_parent' => $parent_id));
			$parent = new TimberPost($parent_id);
			$this->assertEquals(8, count($parent->children()));
		}

		function testPostChildrenOfParentType(){
			$parent_id = $this->factory->post->create(array('post_type' => 'foo'));
			$children = $this->factory->post->create_many(8, array('post_parent' => $parent_id));
			$children = $this->factory->post->create_many(4, array('post_parent' => $parent_id, 'post_type' => 'foo'));
			$parent = new TimberPost($parent_id);
			$this->assertEquals(4, count($parent->children('parent')));
		}

		function testPostNoConstructorArgument(){
			$pid = $this->factory->post->create();
			$this->go_to('?p='.$pid);
			$post = new TimberPost();
			$this->assertEquals($pid, $post->ID);
		}

		function testPostPathUglyPermalinks(){
			update_option('permalink_structure', '');
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$this->assertEquals('http://example.org/?p='.$pid, $post->link());
			$this->assertEquals('/?p='.$pid, $post->path());
		}

		function testPostPathPrettyPermalinks(){
			$struc = '/blog/%year%/%monthnum%/%postname%/';
			update_option('permalink_structure', $struc);
			$pid = $this->factory->post->create(array('post_date' => '2014-05-28'));
			$post = new TimberPost($pid);
			$this->assertStringStartsWith('http://example.org/blog/2014/05/post-title', $post->permalink());
			$this->assertStringStartsWith('/blog/2014/05/post-title', $post->path());
		}

		function testPostCategory(){
			$cat = wp_insert_term('News', 'category');
			$pid = $this->factory->post->create();
			wp_set_object_terms($pid, $cat['term_id'], 'category');
			$post = new TimberPost($pid);
			$this->assertEquals('News', $post->category()->name);
		}

		function testPostCategories() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$category_names = array('News', 'Sports', 'Obits');

			// Uncategorized is applied by default
			$default_categories = $post->categories();
			$this->assertEquals('uncategorized', $default_categories[0]->slug);

			foreach ( $category_names as $category_name ) {
				$category_name = wp_insert_term($category_name, 'category');
				wp_set_object_terms($pid, $category_name['term_id'], 'category', true);
			}

			$this->assertEquals(count($default_categories) + count($category_names), count($post->categories()));
		}

		function testPostTags() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$tag_names = array('News', 'Sports', 'Obits');

			foreach ( $tag_names as $tag_name ) {
				$tag = wp_insert_term($tag_name, 'post_tag');
				wp_set_object_terms($pid, $tag['term_id'], 'post_tag', true);
			}

			$this->assertEquals(count($tag_names), count($post->tags()));
		}

		function testPostTerms() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);

			// create a new tag and associate it with the post
			$dummy_tag = wp_insert_term('whatever', 'post_tag');
			wp_set_object_terms($pid, $dummy_tag['term_id'], 'post_tag', true);

			// test expected tags
			$timber_tags = $post->terms('post_tag');
			$dummy_timber_tag = new TimberTerm($dummy_tag['term_id'], 'post_tag');
			$this->assertEquals('whatever', $timber_tags[0]->slug);
			$this->assertEquals($dummy_timber_tag, $timber_tags[0]);

			// register a custom taxonomy, create some terms in it and associate to post
			register_taxonomy('team', 'post');
			$team_names = array('Patriots', 'Bills', 'Dolphins', 'Jets');

			foreach ( $team_names as $team_name ) {
				$team_term = wp_insert_term($team_name, 'team');
				wp_set_object_terms($pid, $team_term['term_id'], 'team', true);
			}

			$this->assertEquals(count($team_names), count($post->terms('team')));

			// check presence of specific terms
			$this->assertTrue($post->has_term('Uncategorized'));
			$this->assertTrue($post->has_term('whatever'));
			$this->assertTrue($post->has_term('Dolphins'));
			$this->assertTrue($post->has_term('Patriots', 'team'));

			// 4 teams + 1 tag + default category (Uncategorized)
			$this->assertEquals(6, count($post->terms()));

			// test tags method - wrapper for $this->get_terms('tags')
			$this->assertEquals($post->tags(), $post->terms('tag'));
			$this->assertEquals($post->tags(), $post->terms('tags'));
			$this->assertEquals($post->tags(), $post->terms('post_tag'));

			// test categories method - wrapper for $this->get_terms('category')
			$this->assertEquals($post->categories(), $post->terms('category'));
			$this->assertEquals($post->categories(), $post->terms('categories'));

			// test using an array of taxonomies
			$post_tag_terms = $post->terms(array('post_tag'));
			$this->assertEquals(1, count($post_tag_terms));
			$post_team_terms = $post->terms(array('team'));
			$this->assertEquals(count($team_names), count($post_team_terms));

			// test multiple taxonomies
			$post_tag_and_team_terms = $post->terms(array('post_tag','team'));
			$this->assertEquals(count($post_tag_terms) + count($post_team_terms), count($post_tag_and_team_terms));
		}

		function testPostContentLength() {
			$crawl = "The evil leaders of Planet Spaceball having foolishly spuandered their precious atmosphere, have devised a secret plan to take every breath of air away from their peace-loving neighbor, Planet Druidia. Today is Princess Vespa's wedding day. Unbeknownest to the princess, but knowest to us, danger lurks in the stars above...";
			$pid = $this->factory->post->create(array('post_content' => $crawl));
			$post = new TimberPost($pid);
			$content = trim(strip_tags($post->get_content(6)));
			$this->assertEquals("The evil leaders of Planet Spaceball&hellip;", $content);
		}

		function testPostTypeObject() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$pto = $post->get_post_type();
			$this->assertEquals('Posts', $pto->label);
		}

		function testPage() {
			$pid = $this->factory->post->create(array('post_type' => 'page', 'post_title' => 'My Page'));
			$post = new TimberPost($pid);
			$this->assertEquals($pid, $post->ID);
			$this->assertEquals('My Page', $post->title());
		}

		function testEditUrl() {
			$pid = $this->factory->post->create(array('post_author' => 1));
			$post = new TimberPost($pid);
			$edit_url = $post->edit_link();
			$this->assertEquals('', $edit_url);
			wp_set_current_user(1);
			$data = get_userdata(1);
			$this->assertTrue($post->can_edit());
			$this->assertEquals('http://example.org/wp-admin/post.php?post='.$pid.'&amp;action=edit', $post->get_edit_url());
			//
		}

	}
