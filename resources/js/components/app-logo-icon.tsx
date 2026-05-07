import type { SVGAttributes } from 'react';

export default function AppLogoIcon({ className, ...props }: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} className={className} viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="logo-tg" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%"   stopColor="#6366F1" />
                    <stop offset="62%"  stopColor="#A855F7" />
                    <stop offset="100%" stopColor="#F43F5E" />
                </linearGradient>
                <mask id="logo-tm">
                    <rect x="1" y="3" width="30" height="26" rx="3.5" fill="white" />
                    <circle cx="23" cy="3"  r="5" fill="black" />
                    <circle cx="23" cy="29" r="5" fill="black" />
                </mask>
            </defs>
            <rect x="1" y="3" width="30" height="26" rx="3.5" fill="url(#logo-tg)" mask="url(#logo-tm)" />
            <rect x="1" y="3" width="30" height="13" rx="3.5" fill="white" fillOpacity=".08" mask="url(#logo-tm)" />
            <line stroke="white" strokeOpacity=".45" x1="23" y1="8" x2="23" y2="24"
                  strokeWidth="1" strokeDasharray="2,1.5" strokeLinecap="round" />
        </svg>
    );
}
