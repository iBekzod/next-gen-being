<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed 32 realistic tech bloggers with diverse specialties
     */
    public function run(): void
    {
        $bloggers = [
            // --- Backend & Systems Engineers ---
            [
                'name' => 'Marcus Chen',
                'email' => 'marcus.chen@nextgenbeing.com',
                'bio' => 'Senior backend engineer with 12 years building distributed systems. Previously at Stripe and Cloudflare. Obsessed with database performance and API design.',
                'twitter' => 'marcuschen_dev',
                'linkedin' => 'marcuschen',
                'website' => null,
            ],
            [
                'name' => 'Sarah Mitchell',
                'email' => 'sarah.mitchell@nextgenbeing.com',
                'bio' => 'Staff engineer specializing in microservices architecture. Built payment systems processing $2B+ annually. Writes about scalability patterns and Go.',
                'twitter' => 'sarahmitch_eng',
                'linkedin' => 'sarahmitchell-eng',
                'website' => null,
            ],
            [
                'name' => 'Raj Patel',
                'email' => 'raj.patel@nextgenbeing.com',
                'bio' => 'Platform engineer at a Series C startup. Expert in Kubernetes, Terraform, and cloud-native architecture. Contributor to several CNCF projects.',
                'twitter' => 'rajpatel_infra',
                'linkedin' => 'rajpatel-platform',
                'website' => null,
            ],
            [
                'name' => 'Elena Vasquez',
                'email' => 'elena.vasquez@nextgenbeing.com',
                'bio' => 'Database engineer who has tuned PostgreSQL for Fortune 500 companies. Passionate about query optimization, indexing strategies, and data modeling.',
                'twitter' => 'elena_db',
                'linkedin' => 'elenavasquez-data',
                'website' => null,
            ],

            // --- Frontend & Full-Stack ---
            [
                'name' => 'James Park',
                'email' => 'james.park@nextgenbeing.com',
                'bio' => 'Frontend architect with deep expertise in React, Next.js, and TypeScript. Previously led UI teams at Vercel. Speaker at React Conf and JSConf.',
                'twitter' => 'jamespark_ui',
                'linkedin' => 'jamespark-frontend',
                'website' => null,
            ],
            [
                'name' => 'Aisha Rahman',
                'email' => 'aisha.rahman@nextgenbeing.com',
                'bio' => 'Full-stack developer building SaaS products with Laravel and Vue.js. Runs a popular newsletter on modern PHP development and clean architecture.',
                'twitter' => 'aisha_codes',
                'linkedin' => 'aisharahman-dev',
                'website' => null,
            ],
            [
                'name' => 'Tom Brennan',
                'email' => 'tom.brennan@nextgenbeing.com',
                'bio' => 'Performance-obsessed web developer. Core Web Vitals consultant who has optimized sites serving 50M+ monthly visitors. Writes about browser internals.',
                'twitter' => 'tombrennan_perf',
                'linkedin' => 'tombrennan-web',
                'website' => null,
            ],
            [
                'name' => 'Nina Kowalski',
                'email' => 'nina.kowalski@nextgenbeing.com',
                'bio' => 'Design systems engineer bridging design and development. Built component libraries used by 200+ developers. Advocates for accessible, inclusive interfaces.',
                'twitter' => 'nina_systems',
                'linkedin' => 'ninakowalski-design',
                'website' => null,
            ],

            // --- AI & Machine Learning ---
            [
                'name' => 'David Kim',
                'email' => 'david.kim@nextgenbeing.com',
                'bio' => 'ML engineer who shipped production models at scale. Specializes in LLM fine-tuning, RAG pipelines, and AI infrastructure. PhD dropout who chose building over research.',
                'twitter' => 'davidkim_ml',
                'linkedin' => 'davidkim-ai',
                'website' => null,
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@nextgenbeing.com',
                'bio' => 'AI research engineer focused on practical applications of transformer models. Previously at DeepMind. Writes about making AI accessible to everyday developers.',
                'twitter' => 'priya_ai',
                'linkedin' => 'priyasharma-ml',
                'website' => null,
            ],
            [
                'name' => 'Alex Torres',
                'email' => 'alex.torres@nextgenbeing.com',
                'bio' => 'NLP engineer building conversational AI systems. Expert in prompt engineering, vector databases, and semantic search. Open source contributor to LangChain.',
                'twitter' => 'alextorres_nlp',
                'linkedin' => 'alextorres-ai',
                'website' => null,
            ],
            [
                'name' => 'Mei Zhang',
                'email' => 'mei.zhang@nextgenbeing.com',
                'bio' => 'Computer vision engineer working on real-time object detection systems. Shipped models running on edge devices for autonomous vehicles and robotics.',
                'twitter' => 'meizhang_cv',
                'linkedin' => 'meizhang-vision',
                'website' => null,
            ],

            // --- DevOps & Cloud ---
            [
                'name' => 'Chris Okonkwo',
                'email' => 'chris.okonkwo@nextgenbeing.com',
                'bio' => 'Site reliability engineer managing infrastructure for 99.99% uptime. Expert in observability, incident response, and chaos engineering. AWS Solutions Architect.',
                'twitter' => 'chris_sre',
                'linkedin' => 'chrisokonkwo-sre',
                'website' => null,
            ],
            [
                'name' => 'Laura Andersson',
                'email' => 'laura.andersson@nextgenbeing.com',
                'bio' => 'Cloud architect who has migrated enterprise workloads to AWS, GCP, and Azure. Specializes in cost optimization and multi-cloud strategies.',
                'twitter' => 'laura_cloud',
                'linkedin' => 'lauraandersson-cloud',
                'website' => null,
            ],
            [
                'name' => 'Omar Hassan',
                'email' => 'omar.hassan@nextgenbeing.com',
                'bio' => 'DevOps engineer automating everything. Built CI/CD pipelines deploying 500+ times per day. Passionate about GitOps, ArgoCD, and developer experience.',
                'twitter' => 'omar_devops',
                'linkedin' => 'omarhassan-devops',
                'website' => null,
            ],
            [
                'name' => 'Yuki Tanaka',
                'email' => 'yuki.tanaka@nextgenbeing.com',
                'bio' => 'Platform engineer building internal developer platforms. Expert in Docker, Kubernetes, and service mesh. Believes in making infrastructure invisible to developers.',
                'twitter' => 'yuki_platform',
                'linkedin' => 'yukitanaka-platform',
                'website' => null,
            ],

            // --- Security ---
            [
                'name' => 'Daniel Reeves',
                'email' => 'daniel.reeves@nextgenbeing.com',
                'bio' => 'Application security engineer and former penetration tester. Found critical vulnerabilities in major open source projects. OSCP and CISSP certified.',
                'twitter' => 'danielreeves_sec',
                'linkedin' => 'danielreeves-security',
                'website' => null,
            ],
            [
                'name' => 'Fatima Al-Rashid',
                'email' => 'fatima.alrashid@nextgenbeing.com',
                'bio' => 'Security architect specializing in zero-trust networks and identity management. Writes about OAuth, OIDC, and API security for developers who hate reading RFCs.',
                'twitter' => 'fatima_appsec',
                'linkedin' => 'fatimaalrashid-sec',
                'website' => null,
            ],

            // --- Mobile & Cross-Platform ---
            [
                'name' => 'Ryan O\'Sullivan',
                'email' => 'ryan.osullivan@nextgenbeing.com',
                'bio' => 'Mobile engineer who has shipped apps with 10M+ downloads. Expert in React Native, Flutter, and native iOS/Android. Writes about cross-platform architecture.',
                'twitter' => 'ryan_mobile',
                'linkedin' => 'ryanosullivan-mobile',
                'website' => null,
            ],
            [
                'name' => 'Sofia Hernandez',
                'email' => 'sofia.hernandez@nextgenbeing.com',
                'bio' => 'iOS engineer and Swift enthusiast. Previously at Apple on the UIKit team. Writes deep dives into SwiftUI, Combine, and iOS performance optimization.',
                'twitter' => 'sofia_swift',
                'linkedin' => 'sofiahernandez-ios',
                'website' => null,
            ],

            // --- Data Engineering ---
            [
                'name' => 'Ben Goldstein',
                'email' => 'ben.goldstein@nextgenbeing.com',
                'bio' => 'Data engineer building real-time analytics pipelines processing 1B+ events daily. Expert in Apache Kafka, Spark, and data lakehouse architectures.',
                'twitter' => 'bengoldstein_data',
                'linkedin' => 'bengoldstein-data',
                'website' => null,
            ],
            [
                'name' => 'Amara Osei',
                'email' => 'amara.osei@nextgenbeing.com',
                'bio' => 'Analytics engineer bridging data engineering and business intelligence. Expert in dbt, Snowflake, and building data platforms that teams actually use.',
                'twitter' => 'amara_analytics',
                'linkedin' => 'amaraosei-data',
                'website' => null,
            ],

            // --- Blockchain & Web3 ---
            [
                'name' => 'Viktor Petrov',
                'email' => 'viktor.petrov@nextgenbeing.com',
                'bio' => 'Smart contract developer and blockchain architect. Audited protocols managing $500M+ TVL. Writes about Solidity security, DeFi patterns, and zero-knowledge proofs.',
                'twitter' => 'viktor_web3',
                'linkedin' => 'viktorpetrov-blockchain',
                'website' => null,
            ],

            // --- Rust & Systems Programming ---
            [
                'name' => 'Hannah Wright',
                'email' => 'hannah.wright@nextgenbeing.com',
                'bio' => 'Systems programmer writing high-performance code in Rust and C++. Contributor to the Rust compiler. Writes about memory safety, concurrency, and systems design.',
                'twitter' => 'hannah_rust',
                'linkedin' => 'hannahwright-systems',
                'website' => null,
            ],
            [
                'name' => 'Kenji Nakamura',
                'email' => 'kenji.nakamura@nextgenbeing.com',
                'bio' => 'Embedded systems engineer working on IoT and edge computing. Expert in Rust, C, and real-time operating systems. Previously at Tesla Autopilot team.',
                'twitter' => 'kenji_embedded',
                'linkedin' => 'kenjinakamura-embedded',
                'website' => null,
            ],

            // --- Python & Scripting ---
            [
                'name' => 'Rachel Green',
                'email' => 'rachel.green@nextgenbeing.com',
                'bio' => 'Python developer and automation expert. Built ETL pipelines, CLI tools, and FastAPI services. Maintainer of popular open source Python libraries.',
                'twitter' => 'rachel_python',
                'linkedin' => 'rachelgreen-python',
                'website' => null,
            ],

            // --- Gaming & Graphics ---
            [
                'name' => 'Liam Foster',
                'email' => 'liam.foster@nextgenbeing.com',
                'bio' => 'Game developer and graphics programmer. Built rendering engines and physics simulations. Expert in Unity, Unreal Engine, and GPU programming with CUDA.',
                'twitter' => 'liamfoster_gamedev',
                'linkedin' => 'liamfoster-games',
                'website' => null,
            ],

            // --- Architecture & Leadership ---
            [
                'name' => 'Ingrid Larsen',
                'email' => 'ingrid.larsen@nextgenbeing.com',
                'bio' => 'Principal engineer and technical architect. Led platform migrations at unicorn startups. Writes about system design, tech strategy, and engineering leadership.',
                'twitter' => 'ingrid_architect',
                'linkedin' => 'ingridlarsen-architect',
                'website' => null,
            ],
            [
                'name' => 'Michael Adams',
                'email' => 'michael.adams@nextgenbeing.com',
                'bio' => 'Engineering manager turned CTO. 15 years building and scaling engineering teams. Writes about technical decision-making, hiring, and engineering culture.',
                'twitter' => 'michael_cto',
                'linkedin' => 'michaeladams-eng',
                'website' => null,
            ],

            // --- Open Source & Community ---
            [
                'name' => 'Zara Ibrahim',
                'email' => 'zara.ibrahim@nextgenbeing.com',
                'bio' => 'Open source maintainer and developer advocate. Contributor to Node.js, Express, and Fastify. Passionate about developer experience and community building.',
                'twitter' => 'zara_opensource',
                'linkedin' => 'zaraibrahim-oss',
                'website' => null,
            ],

            // --- Quantum & Emerging Tech ---
            [
                'name' => 'Adrian Costa',
                'email' => 'adrian.costa@nextgenbeing.com',
                'bio' => 'Quantum computing researcher exploring practical applications of quantum algorithms. Writes about quantum-classical hybrid computing and post-quantum cryptography.',
                'twitter' => 'adrian_quantum',
                'linkedin' => 'adriancosta-quantum',
                'website' => null,
            ],

            // --- Testing & Quality ---
            [
                'name' => 'Nadia Kuznetsova',
                'email' => 'nadia.kuznetsova@nextgenbeing.com',
                'bio' => 'QA architect and test automation expert. Built testing frameworks used by Fortune 100 companies. Advocates for shift-left testing and quality engineering culture.',
                'twitter' => 'nadia_testing',
                'linkedin' => 'nadiakuznetsova-qa',
                'website' => null,
            ],
        ];

        $bloggerRole = Role::where('slug', 'blogger')->first();

        foreach ($bloggers as $blogger) {
            $user = User::updateOrCreate(
                ['email' => $blogger['email']],
                [
                    'name' => $blogger['name'],
                    'password' => Hash::make('blogger_' . str_replace(' ', '_', strtolower($blogger['name']))),
                    'bio' => $blogger['bio'],
                    'twitter' => $blogger['twitter'],
                    'linkedin' => $blogger['linkedin'],
                    'website' => $blogger['website'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Assign blogger role
            if ($bloggerRole && !$user->roles()->where('role_id', $bloggerRole->id)->exists()) {
                $user->roles()->attach($bloggerRole->id);
            }
        }
    }
}
