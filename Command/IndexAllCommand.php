<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 10/07/17
 * Time: 20:32
 */

namespace EscapeHither\SearchManagerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\Question;

class IndexAllCommand extends ContainerAwareCommand {
    protected $type;

    protected function configure() {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('escapehither:searchmanager:index-all')
            // the short description shown while running "php bin/console list"
            ->setDescription('Populate Elastic search')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to index all ...')//->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // outputs multiple lines to the console (adding "\n" at the end of each line)


    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param                                                   $helper
     * @param null                                              $message
     * @return mixed
     */
    protected function askInput(InputInterface $input, OutputInterface $output, $helper, $message = NULL) {
        if (!empty($message)) {
            $ask = $message;
        }
        else {
            $ask = 'Please enter your ' . $this->type . ' : ';
        }
        $question = new Question($ask);

        $question->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new \Exception('The ' . $this->type . ' cannot be empty');
            }
            return $value;
        });

        $question->setMaxAttempts(3);
        return $helper->ask($input, $output, $question);

    }

    /**
     * @param mixed $type
     */
    protected function setType($type) {
        $this->type = $type;
    }



}