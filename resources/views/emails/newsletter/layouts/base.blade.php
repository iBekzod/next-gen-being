<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $subject ?? config('app.name') }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
    <style>
        /* Reset styles */
        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        /* Base styles */
        body {
            background-color: #f4f7fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f4f7fa;
            padding: 20px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        /* Header styles */
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 40px;
            text-align: center;
        }

        .email-logo {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            text-decoration: none;
            display: inline-block;
        }

        /* Content styles */
        .email-content {
            padding: 40px;
        }

        .email-content h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 20px 0;
            line-height: 1.3;
        }

        .email-content h2 {
            font-size: 22px;
            font-weight: 600;
            color: #2d3748;
            margin: 30px 0 15px 0;
        }

        .email-content h3 {
            font-size: 18px;
            font-weight: 600;
            color: #4a5568;
            margin: 20px 0 10px 0;
        }

        .email-content p {
            margin: 0 0 15px 0;
            color: #4a5568;
            line-height: 1.6;
        }

        .email-content a {
            color: #667eea;
            text-decoration: none;
        }

        .email-content a:hover {
            text-decoration: underline;
        }

        /* Button styles */
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 10px 0;
        }

        .button-secondary {
            background: #48bb78;
        }

        .button-large {
            padding: 18px 48px;
            font-size: 18px;
        }

        /* Post card styles */
        .post-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background-color: #ffffff;
        }

        .post-card-featured {
            background: linear-gradient(135deg, #f6f8fb 0%, #ffffff 100%);
            border: 2px solid #667eea;
        }

        .post-card img {
            width: 100%;
            height: auto;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .post-card h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
            margin: 0 0 10px 0;
        }

        .post-card p {
            font-size: 14px;
            color: #718096;
            margin: 0 0 15px 0;
        }

        .post-meta {
            font-size: 13px;
            color: #a0aec0;
            margin-bottom: 10px;
        }

        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            background-color: #edf2f7;
            color: #4a5568;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
        }

        .premium-badge {
            display: inline-block;
            padding: 4px 12px;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: #ffffff;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Divider */
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }

        /* Footer styles */
        .email-footer {
            background-color: #f7fafc;
            padding: 30px 40px;
            text-align: center;
        }

        .email-footer p {
            margin: 10px 0;
            font-size: 14px;
            color: #718096;
        }

        .email-footer a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #4a5568;
            font-size: 14px;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .email-header,
            .email-content,
            .email-footer {
                padding: 20px !important;
            }

            .email-content h1 {
                font-size: 24px !important;
            }

            .email-content h2 {
                font-size: 20px !important;
            }

            .button {
                display: block !important;
                width: 100% !important;
            }

            .post-card {
                padding: 15px !important;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .email-wrapper {
                background-color: #1a202c !important;
            }
            .email-container {
                background-color: #2d3748 !important;
            }
            .email-content {
                background-color: #2d3748 !important;
            }
            .email-content h1,
            .email-content h2,
            .email-content h3 {
                color: #f7fafc !important;
            }
            .email-content p {
                color: #e2e8f0 !important;
            }
            .post-card {
                background-color: #374151 !important;
                border-color: #4b5563 !important;
            }
            .email-footer {
                background-color: #374151 !important;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" class="email-wrapper" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table role="presentation" class="email-container" width="600" cellspacing="0" cellpadding="0">
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <a href="{{ config('app.url') }}" class="email-logo">
                                {{ config('app.name') }}
                            </a>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="email-content">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <div class="social-links">
                                @if(setting('social_twitter_handle'))
                                <a href="https://twitter.com/{{ setting('social_twitter_handle') }}">Twitter</a>
                                @endif
                                @if(setting('social_linkedin_url'))
                                <a href="{{ setting('social_linkedin_url') }}">LinkedIn</a>
                                @endif
                                @if(setting('social_github_url'))
                                <a href="{{ setting('social_github_url') }}">GitHub</a>
                                @endif
                            </div>

                            <p>
                                <a href="{{ config('app.url') }}">Visit Website</a> |
                                <a href="{{ config('app.url') }}/posts">Browse Articles</a>
                            </p>

                            <p style="font-size: 12px; color: #a0aec0;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>

                            @yield('footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
