<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Checks security issues in your project dependencies.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class SecurityCheckCommand extends Command
{
    private $endPoint = 'https://security.sensiolabs.org/check_lock';
    private $timeout = 20;
    private $vulnerabilitiesCount;

    /**
     * @see Command
     */
    public function configure()
    {
        $this
            ->setName('security:check')
            ->setDefinition(array(
                new InputArgument('lock', InputArgument::OPTIONAL, 'The path to the composer.lock file', 'composer.lock'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'The output format', 'text'),
                new InputOption('end-point', '', InputOption::VALUE_REQUIRED, 'The security checker server URL'),
                new InputOption('timeout', '', InputOption::VALUE_REQUIRED, 'The HTTP timeout'),
            ))
            ->setDescription('Checks security issues in your project dependencies')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command looks for security isssues in the
project dependencies:

<info>php app/console %command.full_name% /path/to/composer.lock</info>

If the <info>composer.lock</info> file is not located at the root of the project,
pass its absolute path as an argument:

<info>php app/console %command.full_name% /path/to/composer.lock</info>

By default, the command displays the result in plain text, but you can also
configure it to output JSON instead by using the <info>--format</info> option:

<info>php app/console %command.full_name% /path/to/composer.lock --format=json</info>
EOF
            );
    }

    /**
     * @see Command
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($endPoint = $input->getOption('end-point')) {
            $this->endPoint = $endPoint;
        }

        if ($timeout = $input->getOption('timeout')) {
            $this->timeout = $timeout;
        }

        try {
            $data = $this->check($input->getArgument('lock'), $input->getOption('format'));
        } catch (\Exception $e) {
            $output->writeln($this->getHelperSet()->get('formatter')->formatBlock($e->getMessage(), 'error', true));

            return 1;
        }

        if ('json' == $input->getOption('format')) {
            $output->write($data);
        } else {
            $this->displayResults($output, $input->getArgument('lock'), json_decode($data, true));
        }

        if ($this->vulnerabilitiesCount > 0) {
            return 1;
        }
    }

    /**
    * Checks the security issues of the dependencies registered in a
    * composer.lock file.
    *
    * @param string $lock   The path to the composer.lock file
    * @param string $format The return format
    *
    * @return mixed The vulnerabilities
    *
    * @throws \InvalidArgumentException When the output format is unsupported
    * @throws \RuntimeException When the lock file does not exist
    * @throws \RuntimeException When curl does not work or is unavailable
    * @throws \RuntimeException When the certificate can not be copied
    */
    public function check($lock, $format)
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('Curl is required to use this command.');
        }

        if (false === $curl = curl_init()) {
            throw new \RuntimeException('Unable to create a new curl handle.');
        }

        if (is_dir($lock) && file_exists($lock.'/composer.lock')) {
            $lock = $lock.'/composer.lock';
        } elseif (preg_match('/composer\.json$/', $lock)) {
            $lock = str_replace('composer.json', 'composer.lock', $lock);
        }

        if (!is_file($lock)) {
            throw new \RuntimeException('Lock file does not exist.');
        }

        $postFields = array('lock' => '@'.$lock);

        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $postFields['lock'] = new \CurlFile($lock);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_URL, $this->endPoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CAINFO, $certFile = $this->getCertFile());

        $response = curl_exec($curl);

        if (false === $response) {
            $error = curl_error($curl);
            curl_close($curl);
            unlink($certFile);

            throw new \RuntimeException(sprintf('An error occurred: %s.', $error));
        }

        $headersSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headersSize);
        $body = substr($response, $headersSize);

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (400 == $statusCode) {
            if ('text' == $format) {
                $error = trim($body);
            } else {
                $data = json_decode($body, true);
                $error = $data['error'];
            }

            curl_close($curl);
            unlink($certFile);

            throw new \InvalidArgumentException($error);
        }

        if (200 != $statusCode) {
            curl_close($curl);
            unlink($certFile);

            throw new \RuntimeException(sprintf('The web service failed for an unknown reason (HTTP %s).', $statusCode));
        }

        curl_close($curl);
        unlink($certFile);

        if (!(preg_match('/X-Alerts: (\d+)/', $headers, $matches) || 2 == count($matches))) {
            throw new \RuntimeException('The web service did not return alerts count.');
        }

        $this->vulnerabilitiesCount = intval($matches[1]);

        return $body;
    }

    /**
     * Displays a security report based on the vulnerabilities found by the
     * security checker.
     *
     * @param  OutputInterface $output
     * @param  string          $lockFilePath    The file path of the checked file
     * @param  array           $vulnerabilities The raw result of the security check
     */
    public function displayResults(OutputInterface $output, $lockFilePath, array $vulnerabilities)
    {
        $output->writeln("\nSecurity Check Report");
        $output->writeln("~~~~~~~~~~~~~~~~~~~~~\n");

        $output->writeln(" * ".$this->getSecurityStatus(count($vulnerabilities)));
        $output->writeln(" * ".count($vulnerabilities)." packages have known vulnerabilities");
        $output->writeln(" * Checked file: ". realpath($lockFilePath));

        if (0 !== count($vulnerabilities)) {
            $output->write("\n");

            foreach ($vulnerabilities as $dependency => $issues) {
                $dependencyFullName = ' <info>'.$dependency.' ('.$issues['version'].')</info>';

                $output->writeln($dependencyFullName);
                $output->writeln(' '.str_repeat('-', strlen($dependencyFullName)-14));

                foreach ($issues['advisories'] as $issue => $details) {
                    $cve = '<comment>'.('' === $details['cve'] ? 'CVE-XXXX-XXXX' : $details['cve']).': </comment>';
                    $output->writeln(" * ".$cve.$details['title']);

                    if ('' !== $details['link']) {
                        $output->writeln("   -> see: ".$details['link']);
                    }

                    $output->write("\n");
                }
            }
        }

        $output->writeln("\n<info>Disclaimer:</info>");
        $output->writeln(" > This checker can only detect vulnerabilities that are referenced");
        $output->writeln(" > in the SensioLabs security advisories database. Execute this");
        $output->writeln(" > command regularly to check the newly discovered vulnerabilities.\n");
    }

    /**
     * It returns the security status based on the number of vulnerabilities
     * found.
     *
     * @param  int $numVulnerabilities Number of vulnerabilities found by the security checker
     * @return string                  The text description of the security status of this project
     */
    public function getSecurityStatus($numVulnerabilities)
    {
        // ANSI color codes
        $colorCodes = array(
            "none"  => "\033[0m",
            "ok"    => "\033[32m",
            "error" => "\033[37;41m",
        );

        $statusType = 0 === $numVulnerabilities ? 'ok' : 'error';
        $statusMessage = 'ok' === $statusType ? 'OK' : 'CRITICAL';

        $lineStart = $this->hasColorSupport() ? $colorCodes[$statusType] : '';
        $lineEnd   = $this->hasColorSupport() ? $colorCodes['none'] : '';

        return $lineStart.'Security Status: '.$statusMessage.$lineEnd;
    }

    /**
     * Checks whether the current console has ANSI color support.
     *
     * @return boolean True if it has color support, false otherwise
     */
    public function hasColorSupport()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
        }

        return function_exists('posix_isatty') && @posix_isatty(STDOUT);
    }

    /**
     * Creates a temporary file to store the security.sensiolabs.org website certificate
     * and returns its file path. Certificate data copied from:
     * https://github.com/sensiolabs/security-checker/blob/master/SensioLabs/Security/Resources/security.sensiolabs.org.crt
     *
     * @return string The certificate file path
     */
    public function getCertFile() {
        $certificateContents =
            "-----BEGIN CERTIFICATE-----\n".
            "MIIEdDCCA1ygAwIBAgIQRL4Mi1AAJLQR0zYq/mUK/TANBgkqhkiG9w0BAQUFADCB\n".
            "lzELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVUMRcwFQYDVQQHEw5TYWx0IExha2Ug\n".
            "Q2l0eTEeMBwGA1UEChMVVGhlIFVTRVJUUlVTVCBOZXR3b3JrMSEwHwYDVQQLExho\n".
            "dHRwOi8vd3d3LnVzZXJ0cnVzdC5jb20xHzAdBgNVBAMTFlVUTi1VU0VSRmlyc3Qt\n".
            "SGFyZHdhcmUwHhcNOTkwNzA5MTgxMDQyWhcNMTkwNzA5MTgxOTIyWjCBlzELMAkG\n".
            "A1UEBhMCVVMxCzAJBgNVBAgTAlVUMRcwFQYDVQQHEw5TYWx0IExha2UgQ2l0eTEe\n".
            "MBwGA1UEChMVVGhlIFVTRVJUUlVTVCBOZXR3b3JrMSEwHwYDVQQLExhodHRwOi8v\n".
            "d3d3LnVzZXJ0cnVzdC5jb20xHzAdBgNVBAMTFlVUTi1VU0VSRmlyc3QtSGFyZHdh\n".
            "cmUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCx98M4P7Sof885glFn\n".
            "0G2f0v9Y8+efK+wNiVSZuTiZFvfgIXlIwrthdBKWHTxqctU8EGc6Oe0rE81m65UJ\n".
            "M6Rsl7HoxuzBdXmcRl6Nq9Bq/bkqVRcQVLMZ8Jr28bFdtqdt++BxF2uiiPsA3/4a\n".
            "MXcMmgF6sTLjKwEHOG7DpV4jvEWbe1DByTCP2+UretNb+zNAHqDVmBe8i4fDidNd\n".
            "oI6yqqr2jmmIBsX6iSHzCJ1pLgkzmykNRg+MzEk0sGlRvfkGzWitZky8PqxhvQqI\n".
            "DsjfPe58BEydCl5rkdbux+0ojatNh4lz0G6k0B4WixThdkQDf2Os5M1JnMWS9Ksy\n".
            "oUhbAgMBAAGjgbkwgbYwCwYDVR0PBAQDAgHGMA8GA1UdEwEB/wQFMAMBAf8wHQYD\n".
            "VR0OBBYEFKFyXyYbKJhDlV0HN9WFlp1L0sNFMEQGA1UdHwQ9MDswOaA3oDWGM2h0\n".
            "dHA6Ly9jcmwudXNlcnRydXN0LmNvbS9VVE4tVVNFUkZpcnN0LUhhcmR3YXJlLmNy\n".
            "bDAxBgNVHSUEKjAoBggrBgEFBQcDAQYIKwYBBQUHAwUGCCsGAQUFBwMGBggrBgEF\n".
            "BQcDBzANBgkqhkiG9w0BAQUFAAOCAQEARxkP3nTGmZev/K0oXnWO6y1n7k57K9cM\n".
            "//bey1WiCuFMVGWTYGufEpytXoMs61quwOQt9ABjHbjAbPLPSbtNk28Gpgoiskli\n".
            "CE7/yMgUsogWXecB5BKV5UU0s4tpvc+0hY91UZ59Ojg6FEgSxvunOxqNDYJAB+gE\n".
            "CJChicsZUN/KHAG8HQQZexB2lzvukJDKxA4fFm517zP4029bHpbj4HR3dHuKom4t\n".
            "3XbWOTCC8KucUvIqx69JXn7HaOWCgchqJ/kniCrVWFCVH/A7HFe7fRQ5YiuayZSS\n".
            "KqMiDP+JJn1fIytH1xUdqWqeUQ0qUZ6B+dQ7XnASfxAynB67nfhmqA==\n".
            "-----END CERTIFICATE-----\n"
        ;

        $certFile = tempnam(sys_get_temp_dir(), 'sls');
        if (false === @file_put_contents($certFile, $certificateContents)) {
            throw new \RuntimeException(sprintf('Unable to copy the SensioLabs website certificate in "%s".', $certFile));
        }

        return $certFile;
    }
}
