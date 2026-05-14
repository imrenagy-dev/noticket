import { Form, Head } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

export default function Register() {
    const [captchaKey, setCaptchaKey] = useState(0);
    const [captchaLoaded, setCaptchaLoaded] = useState(false);
    const [refreshing, setRefreshing] = useState(false);

    function refreshCaptcha() {
        setRefreshing(true);
        setCaptchaLoaded(false);
        setCaptchaKey((k) => k + 1);
    }

    return (
        <>
            <Head title="Register" />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation', 'captcha']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Full name"
                                />
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">Confirm password</Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                />
                                <InputError message={errors.password_confirmation} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="captcha">Verification code</Label>
                                <div className="flex items-center gap-3">
                                    <div className="relative flex-none rounded-md border border-input overflow-hidden shadow-sm">
                                        {!captchaLoaded && (
                                            <div className="absolute inset-0 flex items-center justify-center bg-muted">
                                                <Spinner className="size-4 text-muted-foreground" />
                                            </div>
                                        )}
                                        <img
                                            key={captchaKey}
                                            src={`/captcha?v=${captchaKey}`}
                                            alt="CAPTCHA verification code"
                                            width={200}
                                            height={64}
                                            className="block"
                                            draggable={false}
                                            onLoad={() => {
                                                setCaptchaLoaded(true);
                                                setRefreshing(false);
                                            }}
                                            onError={() => setRefreshing(false)}
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        onClick={refreshCaptcha}
                                        disabled={refreshing}
                                        className="rounded-md p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground disabled:opacity-50"
                                        title="Get a new code"
                                        tabIndex={-1}
                                    >
                                        <RefreshCw className={`size-4 ${refreshing ? 'animate-spin' : ''}`} />
                                    </button>
                                </div>
                                <Input
                                    id="captcha"
                                    type="text"
                                    name="captcha"
                                    required
                                    tabIndex={5}
                                    autoComplete="off"
                                    placeholder="Type the 6 characters above"
                                    className="uppercase tracking-widest font-mono"
                                    maxLength={6}
                                />
                                <InputError message={errors.captcha} />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={6}
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                Create account
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            Already have an account?{' '}
                            <TextLink href={login()} tabIndex={7}>
                                Log in
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = {
    title: 'Create an account',
    description: 'Enter your details below to create your account',
};
