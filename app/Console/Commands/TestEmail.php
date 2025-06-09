<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetCode as PasswordResetCodeMail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email} {--code=123456}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending password reset email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $code = $this->option('code');
        
        $this->info("Testing email sending to: {$email}");
        $this->info("Using code: {$code}");
        
        try {
            Mail::to($email)->send(new PasswordResetCodeMail($code, 'Test User'));
            $this->info('✅ Email sent successfully!');
            
            // Check if email was queued or sent immediately
            $this->info('📧 Check your email inbox and spam folder');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->error('Error details: ' . $e->getTraceAsString());
        }
        
        // Display current mail configuration
        $this->info("\n📋 Current Mail Configuration:");
        $this->table(
            ['Setting', 'Value'],
            [
                ['MAIL_MAILER', config('mail.default')],
                ['MAIL_HOST', config('mail.mailers.smtp.host')],
                ['MAIL_PORT', config('mail.mailers.smtp.port')],
                ['MAIL_USERNAME', config('mail.mailers.smtp.username')],
                ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                ['MAIL_FROM_NAME', config('mail.from.name')],
            ]
        );
        
        return 0;
    }
}