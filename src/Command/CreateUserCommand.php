<?php

namespace App\Command;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';
    private $em;
    private $userRepository;
    private $userPasswordEncoder;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $userPasswordEncoder,
        AdminRepository $adminRepository
    ){
        $this->em = $em;
        $this->adminRepository = $adminRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows you create a user-admin')
            ->setHelp('This command allows you create a user-admin')
            ->addArgument(
                'username', InputArgument::REQUIRED, 'User admin name')
            ->addArgument(
                'email', InputArgument::REQUIRED, 'admin\'s email')
            ->addArgument(
                'password', InputArgument::REQUIRED, 'admin\'s password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<fg=white;bg=cyan>User creator</>');

        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        $user = $this->adminRepository->findOneByUsername($username);
        if(!empty($user)){
            $output->writeln('<error>That user already exists</error>');
            return;
        }

        $user = new Admin();    
        $user->setUsername($username);
        $user->setEmail($email);
        $password = $this->userPasswordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $roles = ['ROLE_ADMIN'];
        $user->setRoles($roles);
        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<fg=white;bg=green>User created!</>');
        return;

    }
}
