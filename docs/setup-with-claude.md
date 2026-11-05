[← Domain Investors](../README.md)

# Set it up with Claude

No technical knowledge needed: [Claude Code](https://code.claude.com/docs/en/overview)
can do the setup for you, on your own computer, and asks you whenever a
decision or a password is yours. It needs a Claude plan; use the desktop app or
the terminal, not the web version, because the site runs on your computer first.

Paste the prompt below into a new session, in the folder where you keep your
projects. Part 1 gives you a working copy of the site on your computer, free of
charge. Part 2 puts it online on Vallic Cloud, which is paid hosting: you see
the price and click to buy it yourself. You can stop after Part 1.

Claude can make mistakes. Read what it asks to run before you approve it, and
never paste a password or an API key into the chat.

```text
Help me set up Domain Investors, my own website for selling my domain names.
I am not technical. The project is at https://github.com/Vallic/domain-investors.
Once you have the files, read README.md and the guides it links to in docs/: they
are the reference. If they disagree with this message, follow them and tell me.

How to work with me:
- Use plain language. One step at a time: say what it is for, then do it.
- Ask before you install software or change anything outside the project folder.
- When something needs my computer password or a sign-in in my browser, tell me
  exactly what to do and wait for me.
- Never ask me to paste a password, API key or recovery code into this chat, and
  never print or commit one. I type them myself: in .ddev/.env for this computer,
  or as secret variables in the Vallic console. Before each commit, check that
  git status lists no file with secrets in it.
- Stop and get my yes before anything that costs money, can't be undone or
  changes what visitors see: buying machines, deploying, changing DNS, taking
  real payments, deleting anything. I make purchases myself.
- If something fails, read the error, try to fix the cause, and tell me in a
  sentence what happened. Don't switch off a security check to get past it. Don't
  guess: check docs/ or https://docs.vallic.com.

Part 1: on my computer
1. Find out which system I use and what is missing. Install Git, a Docker provider
   and DDEV following the official guides:
   https://docs.ddev.com/en/stable/users/install/docker-installation/ and
   https://docs.ddev.com/en/stable/users/install/ddev-installation/
2. Clone the project into a folder named for my store, then run ddev start and
   ddev composer install.
3. Ask me the questions under "The installer's questions" in docs/install.md, a few at
   a time, in everyday words, suggesting an answer to each. I may not know where
   I want to list yet: that can wait.
4. Install with ddev drush site:install domain_investors --yes, with my answers
   as docs/install.md shows. Don't repeat the password it prints.
5. Give me a one-time login with ddev drush user:login, and have me set my own
   password under My account. Show me the site and the dashboard.
6. Help me add my domains (docs/catalog.md). If I would rather look around first,
   offer the demo domains (docs/demo-content.md), and tell me to remove them before
   I open the site. Connecting a registrar is optional (docs/registrars.md); if I
   want it, tell me which lines to add to .ddev/.env myself.
7. Tell me what I have now and what Part 2 involves. Ask whether to go on.

Part 2: online, on Vallic Cloud
1. Before I sign up, tell me what it will cost and recommend the smallest
   configuration that fits vallic.yaml. Use the Vallic connector if you have it
   (https://vallic.com/mcp, free and read-only); estimates exclude tax. Explain
   what can't be changed later (https://docs.vallic.com/start). Unless I want the
   Meilisearch search, remove that service from vallic.yaml to save memory.
2. Put my copy in a private GitHub repository of my own: have me sign in with
   gh auth login, run git remote rename origin upstream, then
   gh repo create NAME --private --source . --remote origin --push.
3. Walk me through https://console.vallic.com, where I do these myself: sign up,
   create the project, configure it as we agreed, install the Vallic GitHub App
   on this one repository, and attach it. Building the production environment is
   the step that buys machines, so I click that when I am ready. Wait for me.
4. Install the Vallic command line
   (https://docs.vallic.com/vallic-cli), have me run vallic login, add my SSH key
   with vallic ssh-key add (make one first if I have none), then check with
   vallic status and vallic validate --against production.
5. Turn on two-factor sign-in without showing the key:
   vallic var set TFA_ENCRYPTION_KEY "$(openssl rand -base64 32)" production --secret
   Any API keys I want to connect, I enter myself under Variables in the console,
   marked secret. Then run vallic var apply production.
6. Set up mail (docs/vallic-cloud.md). Without a relay, no
   email leaves the site, so I would never hear about offers or sales. If I have
   no mail provider yet, tell me this must be done before I start selling.
7. Commit, push, and with my yes run vallic deploy production --wait.
8. Install on the environment: vallic drush production -- site:install
   domain_investors --yes with my answers, adding vallic_cloud to
   domain_investors_setup.extras. If I would rather keep what I built on my computer,
   remove any demo domains, then follow "Move a local
   install" in docs/vallic-cloud.md instead, then clear my two-factor setup with
   vallic drush production -- tfa:reset-user --name=MYNAME, as the key differs.
9. Give me a login with vallic drush production -- user:login
   --uri="$(vallic url production)". Have me set my password and two-factor
   sign-in straight away, and keep the recovery codes somewhere safe.
10. Connect my domain: vallic domain add, tell me which DNS records to add and
    where, then vallic domain verify, and vallic domain primary once it passes.
11. Go through docs/payments.md with me. Payment methods start off or in test mode.
    Help me place a test order. Switch to live only when I say so.
12. Finish with a short summary, without any secrets: my addresses, what is done,
    what is left, what I will pay, and docs/every-day.md in my own words.
```

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
