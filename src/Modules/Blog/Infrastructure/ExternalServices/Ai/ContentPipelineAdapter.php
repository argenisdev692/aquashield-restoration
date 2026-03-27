<?php

declare(strict_types=1);

namespace Modules\Blog\Infrastructure\ExternalServices\Ai;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\AI\Domain\Ports\ResearchPort;
use Modules\AI\Domain\Ports\TextGenerationPort;
use Modules\AI\Domain\ValueObjects\ResearchResult;
use Modules\Blog\Domain\Ports\ContentGenerationPort;
use Modules\Blog\Domain\ValueObjects\GeneratedPostContent;

final class ContentPipelineAdapter implements ContentGenerationPort
{
    public function __construct(
        private readonly TextGenerationPort $textGen,
        private readonly ResearchPort       $research,
    ) {
    }

    public function generate(string $topic, string $niche, int $wordCount): GeneratedPostContent
    {
        Log::info('ContentPipeline: started', ['topic' => $topic, 'niche' => $niche, 'word_count' => $wordCount]);

        $research = $this->research->research($topic);
        Log::info('ContentPipeline: research done', ['sources' => count($research->sources)]);

        $rawContent = $this->generateArticle($topic, $niche, $wordCount, $research);
        $content    = $this->humanize($rawContent);
        Log::info('ContentPipeline: article generated');

        $seo = $this->generateSeoFields($topic, $niche, $content);
        Log::info('ContentPipeline: seo fields generated');

        return new GeneratedPostContent(
            postContent:      $content,
            postTitleSlug:    $seo['post_title_slug'],
            postExcerpt:      $seo['post_excerpt'],
            metaTitle:        $seo['meta_title'],
            metaDescription:  $seo['meta_description'],
            metaKeywords:     $seo['meta_keywords'],
            sources:          $research->sources,
        );
    }

    private function generateArticle(string $topic, string $niche, int $wordCount, ResearchResult $research): string
    {
        $sourcesContext = collect($research->sources)
            ->take(5)
            ->map(fn(array $s, int $i): string =>
                '[Source ' . ($i + 1) . '] ' . $s['title'] . "\nURL: " . $s['url'] . "\n" . $s['snippet'],
            )
            ->implode("\n\n---\n\n");

        $system = <<<SYSTEM
        You are an expert content writer for the {$niche} industry. Your goal is to create articles that pass Google's E-E-A-T standards.

        WRITING RULES (critical):
        1. Vary sentence length: mix short punchy sentences with developed paragraphs.
        2. Use concrete, specific language — never abstract or generic.
        3. Include at least one practical example or real case from the sector.
        4. Use first person when natural ("in my experience", "I've seen that").
        5. Express a nuanced personal opinion — don't be 100% neutral.
        6. AVOID these AI giveaway words: "robust", "comprehensive", "it's worth noting", "in conclusion", "in summary", "undoubtedly", "fundamentally".
        7. Don't start consecutive paragraphs with the same structure.
        8. Include rhetorical questions or conversational transition phrases.
        9. Cite real sources organically within the text, not as a list at the end.
        10. Tone: professional but approachable.

        SEO STRUCTURE (natural):
        - H1: title with main keyword (compelling, not clickbait)
        - Intro: hook in the first 2 sentences, present the problem
        - H2/H3: logical structure that answers search intent
        - Closing: actionable, not a simple "summary of what we've seen"

        Respond ONLY with the article in Markdown. No preamble or explanations.
        SYSTEM;

        $user = <<<USER
        Write an article of ~{$wordCount} words about: **{$topic}**

        RESEARCH CONTEXT (use as factual base):

        Research summary: {$research->summary}

        Reference sources:
        {$sourcesContext}

        Use these real data points to give depth to the article. Cite sources organically.
        USER;

        return $this->textGen->generate($system, $user, 4096)->content;
    }

    private function generateSeoFields(string $topic, string $niche, string $content): array
    {
        $excerpt = mb_substr(strip_tags($content), 0, 300);

        $system = 'You are an SEO expert. You respond ONLY with a valid JSON object, no markdown fences, no commentary.';

        $user = <<<USER
        Based on this article about "{$topic}" in the "{$niche}" niche, generate the following SEO fields.

        Article preview (first 300 chars):
        {$excerpt}

        Return ONLY a JSON object with exactly these keys:
        {
          "post_title_slug": "kebab-case-slug-max-60-chars",
          "post_excerpt": "Compelling 1-2 sentence summary, 120-160 chars, no AI clichés",
          "meta_title": "SEO title under 60 chars including main keyword",
          "meta_description": "Meta description 140-160 chars, includes keyword, compelling CTA",
          "meta_keywords": "keyword1, keyword2, keyword3, keyword4, keyword5"
        }
        USER;

        $raw     = $this->textGen->generate($system, $user, 512)->content;
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            preg_match('/\{.*\}/s', $raw, $matches);
            $decoded = isset($matches[0]) ? json_decode($matches[0], true) : null;
        }

        $slug = Str::slug($topic);

        return [
            'post_title_slug'  => is_string($decoded['post_title_slug'] ?? null)  ? $decoded['post_title_slug']  : $slug,
            'post_excerpt'     => is_string($decoded['post_excerpt'] ?? null)     ? $decoded['post_excerpt']     : mb_substr(strip_tags($raw), 0, 160),
            'meta_title'       => is_string($decoded['meta_title'] ?? null)       ? $decoded['meta_title']       : $topic,
            'meta_description' => is_string($decoded['meta_description'] ?? null) ? $decoded['meta_description'] : '',
            'meta_keywords'    => is_string($decoded['meta_keywords'] ?? null)    ? $decoded['meta_keywords']    : '',
        ];
    }

    private function humanize(string $content): string
    {
        $replacements = [
            '/\bit\'s worth noting that\b/i'  => 'importantly,',
            '/\bin conclusion\b/i'             => 'to close,',
            '/\bin summary\b/i'                => 'to sum up,',
            '/\bundoubtedly\b/i'               => 'clearly,',
            '/\bfundamentally\b/i'             => 'essentially,',
            '/\bin this sense\b/i'             => '',
            '/\bin the realm of\b/i'           => 'in',
            '/\brobustness\b/i'                => 'reliability',
            '/\brobust\b/i'                    => 'solid',
            '/\bcomprehensive\b/i'             => 'complete',
            '/\bparadigm\b/i'                  => 'approach',
            '/\boptimize\b/i'                  => 'improve',
            '/\bleverage\b/i'                  => 'use',
            '/\bempower\b/i'                   => 'enable',
            '/\bseamless(ly)?\b/i'             => 'smooth',
            '/\bdelve\b/i'                     => 'explore',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $content = (string) preg_replace($pattern, $replacement, $content);
        }

        return trim($content);
    }
}
