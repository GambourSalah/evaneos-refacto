<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {
            $_quoteFromRepository = FactoryRepository::generateEntityFromRepository(QuoteRepository::class, $quote->id);
            $usefulObject = FactoryRepository::generateEntityFromRepository(SiteRepository::class, $quote->siteId);
            $destinationOfQuote = FactoryRepository::generateEntityFromRepository(DestinationRepository::class, $quote->destinationId);

            if(strpos($text, '[quote:destination_link]') !== false){
                $destination = FactoryRepository::generateEntityFromRepository(DestinationRepository::class, $quote->destinationId);
            }

            $containsSummaryHtml = strpos($text, '[quote:summary_html]');
            $containsSummary     = strpos($text, '[quote:summary]');

            if ($containsSummaryHtml !== false || $containsSummary !== false) {
                if ($containsSummaryHtml !== false) {
                    $text = $this->getSummaryRender('[quote:summary_html]', $_quoteFromRepository, $text, true);
                }
                if ($containsSummary !== false) {
                    $text = $this->getSummaryRender('[quote:summary_html]', $_quoteFromRepository, $text);
                }
            }

            (strpos($text, '[quote:destination_name]') !== false) and $text = str_replace('[quote:destination_name]',$destinationOfQuote->countryName,$text);
        }

        if (isset($destination))
            $text = str_replace('[quote:destination_link]', $usefulObject->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id, $text);
        else
            $text = str_replace('[quote:destination_link]', '', $text);

        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]'       , ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

    /**
     * Get the summary render in text/html format
     * @param  [string]  $placeholder [Placeholder]
     * @param  [object]  $entity      [Entity]
     * @param  [string]  $text        [String to format]
     * @param  boolean $html        [Render in HTML format or not]
     * @return [string]               [Formatted render]
     */
    public function getSummaryRender($placeholder, $entity, $text, $html = false) {
        if(!$html) {
            return str_replace($placeholder, Quote::renderText($entity), $text);
        }
        return str_replace($placeholder, Quote::renderHtml($entity), $text);
    }
}
