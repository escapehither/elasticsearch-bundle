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
    const EMAIL = 'mail';
    const PASSWORD = 'password';
    const USERNAME = 'username';

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
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);
        $inputList = [
            self::USERNAME,
            self::EMAIL,
            self::PASSWORD,
        ];

        $helper = $this->getHelper('question');
        $data = [];
        foreach ($inputList as $value) {
            $this->setType($value);
            $data[$value] = $this->askInput($input, $output, $helper);
        }
        $message = 'repeat your password :';
        $repeatPassword = $this->askInput($input, $output, $helper, $message);
        if ($data[self::PASSWORD] != $repeatPassword) {
            throw new \Exception('The password is not valid');
        }
        $this->createUser($data);
        // outputs a message followed by a "\n"
        $output->writeln($data[self::PASSWORD]);
        $output->writeln('The user : ' .$data[self::USERNAME].' successfully created');

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
        if ($this->type == self::PASSWORD) {
            $question->setHidden(TRUE);
        }
        $question->setMaxAttempts(3);
        return $helper->ask($input, $output, $question);

    }

    /**
     * @param mixed $type
     */
    protected function setType($type) {
        $this->type = $type;
    }

    protected function createUser($data) {
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $user_provider_class = $container->getParameter('escape_hither.security.user.class');
        $repository = $em->getRepository($user_provider_class);
        $previousByMail = $repository->findOneByEmail($data[self::EMAIL]);
        $previousByUserName = $repository->findOneByUsername($data[self::USERNAME]);
        if ($previousByMail || $previousByUserName) {
            throw new \Exception('The  user already exist');
        }
        $user = new $user_provider_class();
        $user->setUsername($data[self::USERNAME]);
        $user->setEmail($data[self::EMAIL]);
        $user->setPlainPassword($data[self::PASSWORD]);
        if (!empty($roles)) {
            $user->setRoles($roles);
        }
        $em->persist($user);
        $em->flush();
    }


}