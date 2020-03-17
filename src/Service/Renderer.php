<?php
/**
 * @author      Matthias Büsing <info@mb-tec.eu>
 * @copyright   2020 Matthias Büsing
 * @license     GPL-2.0
 * @link        http://mb-tec.eu
 */

declare(strict_types=1);

namespace MBtec\LaminasEmail\Service;

use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Html2Text\Html2Text;

/**
 * Class Renderer
 * @package MBtec\LaminasEmail\Service
 */
class Renderer
{
    /**
     * @var PhpRenderer
     */
    private $renderer;

    /**
     * Renderer constructor.
     * @param PhpRenderer $renderer
     */
    public function __construct(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param string $tpl
     * @param array $vars
     * @param array $options
     * @return array
     */
    public function renderTemplate(string $tpl, array $vars, array $options): array
    {
        $mailData = $this->getMailData($tpl, $vars);

        // Add Footer
        if (isset($options['add_footer']) && $options['add_footer']) {
            $templateNameData = explode('/', $tpl);
            $footerTpl = sprintf('%s/email/_footer', $templateNameData[0]);

            $footerData = $this->getMailData($footerTpl, $vars);

            $mailData['html'] .= $footerData['html'];
            $mailData['plain'] .= $footerData['plain'];
        }

        return $mailData;
    }

    /**
     * @param string $tpl
     * @param array $vars
     * @return array
     */
    protected function getMailData(string $tpl, array $vars = []): array
    {
        $subject = $html = $plain = null;

        $tplData = explode('######', $this->renderMail($tpl, $vars));
        $tplData = array_map('trim', $tplData);

        foreach ($tplData as $row) {
            $parts = array_map('trim', explode(':', $row, 2));

            switch (strtolower($parts[0])) {
                case 'subject':
                    $subject = $parts[1];
                    break;

                case 'html':
                    $html = $parts[1];
                    break;

                case 'plain':
                    $plain = $parts[1];
                    break;

                default:
            }
        }

        if ($subject === null) {
            $subject = 'Missing subject';
        }

        if ($html === null) {
            $html = 'Missing html content';
        }

        if ($plain === null) {
            /** @var Html2Text $html2Text */
            $html2Text = new Html2Text($html);
            $plain = $html2Text->getText();
        }

        return [
            'subject' => $subject,
            'html' => $html,
            'plain' => $plain,
        ];
    }

    /**
     * @param string $tpl
     * @param array $vars
     * @return string
     */
    protected function renderMail(string $tpl, array $vars = []): string
    {
        $viewModel = new ViewModel($vars);
        $viewModel->setTemplate($tpl);

        return $this->renderer->render($viewModel);
    }
}
