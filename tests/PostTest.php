<?php 

namespace Lumenpress\ORM\Tests;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Lumenpress\ORM\Models\Post;

class PostTest extends TestCase
{
    public function testCreatingPost()
    {
        $post = new Post;
        $post->title = 'test creating post';

        $this->assertTrue($post->save());
    }

    public function testCreatingPage()
    {
        $post = new Post;
        $post->type = 'page';
        $post->title = 'test creating page';
        $post->save();

        $page = Post::find($post->ID);

        $this->assertTrue($post->type  == 'page' && $page->type == 'page');
    }

    public function testPostSlug()
    {
        $title = 'test post slug';

        for ($i=1; $i < 6; $i++) { 
            $post = new Post;
            $post->title = $title;
            $post->save();
            $this->assertTrue($post->post_name == $post->slug);
            $this->assertTrue(Str::slug($title).($i==1?'':'-'.$i) == $post->slug);
        }
    }

    public function testPostDates()
    {
        $post = new Post;
        $post->title = 'test post timestamps';
        // $post->author_id = 1;
        $post->save();

        $this->assertEquals($post->type, 'post');
        $this->assertInstanceOf(Carbon::class, $post->date);
        $this->assertInstanceOf(Carbon::class, $post->date_gmt);
        $this->assertInstanceOf(Carbon::class, $post->modified);
        $this->assertInstanceOf(Carbon::class, $post->modified_gmt);

        $this->assertEquals((string)$post->date->timezone('UTC'), (string)$post->date_gmt);
        $this->assertEquals((string)$post->modified->timezone('UTC'), (string)$post->modified_gmt);

        $post = Post::find($post->ID);

        $this->assertEquals($post->type, 'post');
        $this->assertInstanceOf(Carbon::class, $post->date);
        $this->assertInstanceOf(Carbon::class, $post->date_gmt);
        $this->assertInstanceOf(Carbon::class, $post->modified);
        $this->assertInstanceOf(Carbon::class, $post->modified_gmt);

        $this->assertEquals((string)$post->date->timezone('UTC'), (string)$post->date_gmt);
        $this->assertEquals((string)$post->modified->timezone('UTC'), (string)$post->modified_gmt);
    }
}
