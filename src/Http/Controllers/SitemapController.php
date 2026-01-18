<?php

namespace Flectar\Sitemap\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Waterhole\Models\Channel;
use Waterhole\Models\Post;
use Waterhole\Models\Page;
use Waterhole\Models\User;

class SitemapController extends Controller
{
    protected $cacheTime = 86400;

    public function index()
    {
        $urls = Cache::remember("sitemap.all", $this->cacheTime, function () {
            return array_merge(
                $this->getStaticUrls(),
                $this->getChannelUrls(),
                $this->getPostUrls(),
                $this->getPageUrls(),
                $this->getUserUrls(),
            );
        });

        return $this->render($urls);
    }

    public function posts()
    {
        $urls = Cache::remember("sitemap.posts", $this->cacheTime, function () {
            return $this->getPostUrls();
        });

        return $this->render($urls);
    }

    public function channels()
    {
        $urls = Cache::remember(
            "sitemap.channels",
            $this->cacheTime,
            function () {
                return $this->getChannelUrls();
            },
        );

        return $this->render($urls);
    }

    public function pages()
    {
        $urls = Cache::remember("sitemap.pages", $this->cacheTime, function () {
            return $this->getPageUrls();
        });

        return $this->render($urls);
    }

    public function users()
    {
        $urls = Cache::remember("sitemap.users", $this->cacheTime, function () {
            return $this->getUserUrls();
        });

        return $this->render($urls);
    }

    protected function render(array $urls)
    {
        return Response::view("waterhole-sitemap::index", [
            "urls" => $urls,
        ])->header("Content-Type", "text/xml");
    }

    protected function formatUrl($loc, $lastmod, $changefreq, $priority)
    {
        return [
            "loc" => $loc,
            "lastmod" => $lastmod->toAtomString(),
            "changefreq" => $changefreq,
            "priority" => $priority,
        ];
    }

    protected function getStaticUrls()
    {
        return [$this->formatUrl(url("/"), now(), "daily", "1.0")];
    }

    protected function getChannelUrls()
    {
        return Channel::all()
            ->map(function ($channel) {
                $lastmod =
                    $channel->updated_at ?? ($channel->created_at ?? now());
                return $this->formatUrl(
                    url("/channels/{$channel->slug}"),
                    $lastmod,
                    "weekly",
                    "0.8",
                );
            })
            ->all();
    }

    protected function getPostUrls()
    {
        return Post::latest("id")
            ->limit(1000)
            ->get()
            ->map(function ($post) {
                $lastmod = $post->updated_at ?? ($post->created_at ?? now());
                return $this->formatUrl(
                    url("/posts/{$post->id}"),
                    $lastmod,
                    "monthly",
                    "0.6",
                );
            })
            ->all();
    }

    protected function getPageUrls()
    {
        return Page::all()
            ->map(function ($page) {
                $lastmod = $page->updated_at ?? ($page->created_at ?? now());
                return $this->formatUrl(
                    url("/{$page->slug}"),
                    $lastmod,
                    "monthly",
                    "0.7",
                );
            })
            ->all();
    }

    protected function getUserUrls()
    {
        return User::limit(500)
            ->get()
            ->map(function ($user) {
                $lastmod = $user->updated_at ?? ($user->created_at ?? now());
                return $this->formatUrl(
                    url("/u/{$user->id}"),
                    $lastmod,
                    "weekly",
                    "0.5",
                );
            })
            ->all();
    }
}
