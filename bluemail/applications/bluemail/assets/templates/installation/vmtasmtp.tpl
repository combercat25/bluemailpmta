<virtual-mta $P{VMTA}>
    <domain *>
    auth-username $P{USERNAME}
    auth-password $P{PASSWORD}
    route $P{SMTPHOST}
    use-starttls yes
    </domain>
    smtp-source-host $P{IP} $P{DOMAIN}
    $P{DKIM}
</virtual-mta>