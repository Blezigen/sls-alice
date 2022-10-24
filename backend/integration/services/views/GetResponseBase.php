<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mes="urn://artefacts-russiatourism-ru/services/message-exchange" xmlns:typ="urn://artefacts-russiatourism-ru/services/message-exchange/types" xmlns:bas="urn://artefacts-russiatourism-ru/services/message-exchange/types/basic">
    <soapenv:Header />
    <soapenv:Body>
        <mes:GetResponse>
            <typ:GetResponseRequest>
                <typ:SenderProvidedGetResponseData>
                    <typ:Sender>
                        <typ:Mnemonic><?= $mnemonic ?></typ:Mnemonic>
                        <typ:HumanReadableName><?= $humanReadableName ?></typ:HumanReadableName>
                    </typ:Sender>
                    <bas:RequestReference>
                        <bas:RequestId><?= $requestId ?></bas:RequestId>
                    </bas:RequestReference>
                </typ:SenderProvidedGetResponseData>
            </typ:GetResponseRequest>
        </mes:GetResponse>
    </soapenv:Body>
</soapenv:Envelope>