<?php
// This file was auto-generated from sdk-root/src/data/kms/2014-11-01/api-2.json
return [ 'version' => '2.0', 'metadata' => [ 'apiVersion' => '2014-11-01', 'endpointPrefix' => 'kms', 'jsonVersion' => '1.1', 'protocol' => 'json', 'serviceAbbreviation' => 'KMS', 'serviceFullName' => 'AWS Key Management Service', 'serviceId' => 'KMS', 'signatureVersion' => 'v4', 'targetPrefix' => 'TrentService', 'uid' => 'kms-2014-11-01', ], 'operations' => [ 'CancelKeyDeletion' => [ 'name' => 'CancelKeyDeletion', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'CancelKeyDeletionRequest', ], 'output' => [ 'shape' => 'CancelKeyDeletionResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'CreateAlias' => [ 'name' => 'CreateAlias', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'CreateAliasRequest', ], 'errors' => [ [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'AlreadyExistsException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidAliasNameException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'LimitExceededException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'CreateGrant' => [ 'name' => 'CreateGrant', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'CreateGrantRequest', ], 'output' => [ 'shape' => 'CreateGrantResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'InvalidGrantTokenException', ], [ 'shape' => 'LimitExceededException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'CreateKey' => [ 'name' => 'CreateKey', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'CreateKeyRequest', ], 'output' => [ 'shape' => 'CreateKeyResponse', ], 'errors' => [ [ 'shape' => 'MalformedPolicyDocumentException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'UnsupportedOperationException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'LimitExceededException', ], [ 'shape' => 'TagException', ], ], ], 'Decrypt' => [ 'name' => 'Decrypt', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'DecryptRequest', ], 'output' => [ 'shape' => 'DecryptResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'InvalidCiphertextException', ], [ 'shape' => 'KeyUnavailableException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidGrantTokenException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'DeleteAlias' => [ 'name' => 'DeleteAlias', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'DeleteAliasRequest', ], 'errors' => [ [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'DeleteImportedKeyMaterial' => [ 'name' => 'DeleteImportedKeyMaterial', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'DeleteImportedKeyMaterialRequest', ], 'errors' => [ [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'UnsupportedOperationException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'DescribeKey' => [ 'name' => 'DescribeKey', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'DescribeKeyRequest', ], 'output' => [ 'shape' => 'DescribeKeyResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], ], ], 'DisableKey' => [ 'name' => 'DisableKey', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'DisableKeyRequest', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'DisableKeyRotation' => [ 'name' => 'DisableKeyRotation', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'DisableKeyRotationRequest', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], [ 'shape' => 'UnsupportedOperationException', ], ], ], 'EnableKey' => [ 'name' => 'EnableKey', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'EnableKeyRequest', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'LimitExceededException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'EnableKeyRotation' => [ 'name' => 'EnableKeyRotation', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'EnableKeyRotationRequest', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], [ 'shape' => 'UnsupportedOperationException', ], ], ], 'Encrypt' => [ 'name' => 'Encrypt', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'EncryptRequest', ], 'output' => [ 'shape' => 'EncryptResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'KeyUnavailableException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidKeyUsageException', ], [ 'shape' => 'InvalidGrantTokenException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'GenerateDataKey' => [ 'name' => 'GenerateDataKey', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'GenerateDataKeyRequest', ], 'output' => [ 'shape' => 'GenerateDataKeyResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'KeyUnavailableException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidKeyUsageException', ], [ 'shape' => 'InvalidGrantTokenException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'GenerateDataKeyWithoutPlaintext' => [ 'name' => 'GenerateDataKeyWithoutPlaintext', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'GenerateDataKeyWithoutPlaintextRequest', ], 'output' => [ 'shape' => 'GenerateDataKeyWithoutPlaintextResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'KeyUnavailableException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidKeyUsageException', ], [ 'shape' => 'InvalidGrantTokenException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'GenerateRandom' => [ 'name' => 'GenerateRandom', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'GenerateRandomRequest', ], 'output' => [ 'shape' => 'GenerateRandomResponse', ], 'errors' => [ [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], ], ], 'GetKeyPolicy' => [ 'name' => 'GetKeyPolicy', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'GetKeyPolicyRequest', ], 'output' => [ 'shape' => 'GetKeyPolicyResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'GetKeyRotationStatus' => [ 'name' => 'GetKeyRotationStatus', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'GetKeyRotationStatusRequest', ], 'output' => [ 'shape' => 'GetKeyRotationStatusResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], [ 'shape' => 'UnsupportedOperationException', ], ], ], 'GetParametersForImport' => [ 'name' => 'GetParametersForImport', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'GetParametersForImportRequest', ], 'output' => [ 'shape' => 'GetParametersForImportResponse', ], 'errors' => [ [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'UnsupportedOperationException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'ImportKeyMaterial' => [ 'name' => 'ImportKeyMaterial', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ImportKeyMaterialRequest', ], 'output' => [ 'shape' => 'ImportKeyMaterialResponse', ], 'errors' => [ [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'UnsupportedOperationException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], [ 'shape' => 'InvalidCiphertextException', ], [ 'shape' => 'IncorrectKeyMaterialException', ], [ 'shape' => 'ExpiredImportTokenException', ], [ 'shape' => 'InvalidImportTokenException', ], ], ], 'ListAliases' => [ 'name' => 'ListAliases', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ListAliasesRequest', ], 'output' => [ 'shape' => 'ListAliasesResponse', ], 'errors' => [ [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidMarkerException', ], [ 'shape' => 'KMSInternalException', ], ], ], 'ListGrants' => [ 'name' => 'ListGrants', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ListGrantsRequest', ], 'output' => [ 'shape' => 'ListGrantsResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidMarkerException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'ListKeyPolicies' => [ 'name' => 'ListKeyPolicies', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ListKeyPoliciesRequest', ], 'output' => [ 'shape' => 'ListKeyPoliciesResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'ListKeys' => [ 'name' => 'ListKeys', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ListKeysRequest', ], 'output' => [ 'shape' => 'ListKeysResponse', ], 'errors' => [ [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'InvalidMarkerException', ], ], ], 'ListResourceTags' => [ 'name' => 'ListResourceTags', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ListResourceTagsRequest', ], 'output' => [ 'shape' => 'ListResourceTagsResponse', ], 'errors' => [ [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'InvalidMarkerException', ], ], ], 'ListRetirableGrants' => [ 'name' => 'ListRetirableGrants', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ListRetirableGrantsRequest', ], 'output' => [ 'shape' => 'ListGrantsResponse', ], 'errors' => [ [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidMarkerException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'KMSInternalException', ], ], ], 'PutKeyPolicy' => [ 'name' => 'PutKeyPolicy', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'PutKeyPolicyRequest', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'MalformedPolicyDocumentException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'UnsupportedOperationException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'LimitExceededException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'ReEncrypt' => [ 'name' => 'ReEncrypt', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ReEncryptRequest', ], 'output' => [ 'shape' => 'ReEncryptResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DisabledException', ], [ 'shape' => 'InvalidCiphertextException', ], [ 'shape' => 'KeyUnavailableException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidKeyUsageException', ], [ 'shape' => 'InvalidGrantTokenException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'RetireGrant' => [ 'name' => 'RetireGrant', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'RetireGrantRequest', ], 'errors' => [ [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'InvalidGrantTokenException', ], [ 'shape' => 'InvalidGrantIdException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'RevokeGrant' => [ 'name' => 'RevokeGrant', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'RevokeGrantRequest', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'InvalidGrantIdException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'ScheduleKeyDeletion' => [ 'name' => 'ScheduleKeyDeletion', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'ScheduleKeyDeletionRequest', ], 'output' => [ 'shape' => 'ScheduleKeyDeletionResponse', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'TagResource' => [ 'name' => 'TagResource', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'TagResourceRequest', ], 'errors' => [ [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'KMSInvalidStateException', ], [ 'shape' => 'LimitExceededException', ], [ 'shape' => 'TagException', ], ], ], 'UntagResource' => [ 'name' => 'UntagResource', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'UntagResourceRequest', ], 'errors' => [ [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'KMSInvalidStateException', ], [ 'shape' => 'TagException', ], ], ], 'UpdateAlias' => [ 'name' => 'UpdateAlias', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'UpdateAliasRequest', ], 'errors' => [ [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'NotFoundException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], 'UpdateKeyDescription' => [ 'name' => 'UpdateKeyDescription', 'http' => [ 'method' => 'POST', 'requestUri' => '/', ], 'input' => [ 'shape' => 'UpdateKeyDescriptionRequest', ], 'errors' => [ [ 'shape' => 'NotFoundException', ], [ 'shape' => 'InvalidArnException', ], [ 'shape' => 'DependencyTimeoutException', ], [ 'shape' => 'KMSInternalException', ], [ 'shape' => 'KMSInvalidStateException', ], ], ], ], 'shapes' => [ 'AWSAccountIdType' => [ 'type' => 'string', ], 'AlgorithmSpec' => [ 'type' => 'string', 'enum' => [ 'RSAES_PKCS1_V1_5', 'RSAES_OAEP_SHA_1', 'RSAES_OAEP_SHA_256', ], ], 'AliasList' => [ 'type' => 'list', 'member' => [ 'shape' => 'AliasListEntry', ], ], 'AliasListEntry' => [ 'type' => 'structure', 'members' => [ 'AliasName' => [ 'shape' => 'AliasNameType', ], 'AliasArn' => [ 'shape' => 'ArnType', ], 'TargetKeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'AliasNameType' => [ 'type' => 'string', 'max' => 256, 'min' => 1, 'pattern' => '^[a-zA-Z0-9:/_-]+$', ], 'AlreadyExistsException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'ArnType' => [ 'type' => 'string', 'max' => 2048, 'min' => 20, ], 'BooleanType' => [ 'type' => 'boolean', ], 'CancelKeyDeletionRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'CancelKeyDeletionResponse' => [ 'type' => 'structure', 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'CiphertextType' => [ 'type' => 'blob', 'max' => 6144, 'min' => 1, ], 'CreateAliasRequest' => [ 'type' => 'structure', 'required' => [ 'AliasName', 'TargetKeyId', ], 'members' => [ 'AliasName' => [ 'shape' => 'AliasNameType', ], 'TargetKeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'CreateGrantRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'GranteePrincipal', 'Operations', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'GranteePrincipal' => [ 'shape' => 'PrincipalIdType', ], 'RetiringPrincipal' => [ 'shape' => 'PrincipalIdType', ], 'Operations' => [ 'shape' => 'GrantOperationList', ], 'Constraints' => [ 'shape' => 'GrantConstraints', ], 'GrantTokens' => [ 'shape' => 'GrantTokenList', ], 'Name' => [ 'shape' => 'GrantNameType', ], ], ], 'CreateGrantResponse' => [ 'type' => 'structure', 'members' => [ 'GrantToken' => [ 'shape' => 'GrantTokenType', ], 'GrantId' => [ 'shape' => 'GrantIdType', ], ], ], 'CreateKeyRequest' => [ 'type' => 'structure', 'members' => [ 'Policy' => [ 'shape' => 'PolicyType', ], 'Description' => [ 'shape' => 'DescriptionType', ], 'KeyUsage' => [ 'shape' => 'KeyUsageType', ], 'Origin' => [ 'shape' => 'OriginType', ], 'BypassPolicyLockoutSafetyCheck' => [ 'shape' => 'BooleanType', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'CreateKeyResponse' => [ 'type' => 'structure', 'members' => [ 'KeyMetadata' => [ 'shape' => 'KeyMetadata', ], ], ], 'DataKeySpec' => [ 'type' => 'string', 'enum' => [ 'AES_256', 'AES_128', ], ], 'DateType' => [ 'type' => 'timestamp', ], 'DecryptRequest' => [ 'type' => 'structure', 'required' => [ 'CiphertextBlob', ], 'members' => [ 'CiphertextBlob' => [ 'shape' => 'CiphertextType', ], 'EncryptionContext' => [ 'shape' => 'EncryptionContextType', ], 'GrantTokens' => [ 'shape' => 'GrantTokenList', ], ], ], 'DecryptResponse' => [ 'type' => 'structure', 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Plaintext' => [ 'shape' => 'PlaintextType', ], ], ], 'DeleteAliasRequest' => [ 'type' => 'structure', 'required' => [ 'AliasName', ], 'members' => [ 'AliasName' => [ 'shape' => 'AliasNameType', ], ], ], 'DeleteImportedKeyMaterialRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'DependencyTimeoutException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, 'fault' => true, ], 'DescribeKeyRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'GrantTokens' => [ 'shape' => 'GrantTokenList', ], ], ], 'DescribeKeyResponse' => [ 'type' => 'structure', 'members' => [ 'KeyMetadata' => [ 'shape' => 'KeyMetadata', ], ], ], 'DescriptionType' => [ 'type' => 'string', 'max' => 8192, 'min' => 0, ], 'DisableKeyRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'DisableKeyRotationRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'DisabledException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'EnableKeyRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'EnableKeyRotationRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'EncryptRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'Plaintext', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Plaintext' => [ 'shape' => 'PlaintextType', ], 'EncryptionContext' => [ 'shape' => 'EncryptionContextType', ], 'GrantTokens' => [ 'shape' => 'GrantTokenList', ], ], ], 'EncryptResponse' => [ 'type' => 'structure', 'members' => [ 'CiphertextBlob' => [ 'shape' => 'CiphertextType', ], 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'EncryptionContextKey' => [ 'type' => 'string', ], 'EncryptionContextType' => [ 'type' => 'map', 'key' => [ 'shape' => 'EncryptionContextKey', ], 'value' => [ 'shape' => 'EncryptionContextValue', ], ], 'EncryptionContextValue' => [ 'type' => 'string', ], 'ErrorMessageType' => [ 'type' => 'string', ], 'ExpirationModelType' => [ 'type' => 'string', 'enum' => [ 'KEY_MATERIAL_EXPIRES', 'KEY_MATERIAL_DOES_NOT_EXPIRE', ], ], 'ExpiredImportTokenException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'GenerateDataKeyRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'EncryptionContext' => [ 'shape' => 'EncryptionContextType', ], 'NumberOfBytes' => [ 'shape' => 'NumberOfBytesType', ], 'KeySpec' => [ 'shape' => 'DataKeySpec', ], 'GrantTokens' => [ 'shape' => 'GrantTokenList', ], ], ], 'GenerateDataKeyResponse' => [ 'type' => 'structure', 'members' => [ 'CiphertextBlob' => [ 'shape' => 'CiphertextType', ], 'Plaintext' => [ 'shape' => 'PlaintextType', ], 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'GenerateDataKeyWithoutPlaintextRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'EncryptionContext' => [ 'shape' => 'EncryptionContextType', ], 'KeySpec' => [ 'shape' => 'DataKeySpec', ], 'NumberOfBytes' => [ 'shape' => 'NumberOfBytesType', ], 'GrantTokens' => [ 'shape' => 'GrantTokenList', ], ], ], 'GenerateDataKeyWithoutPlaintextResponse' => [ 'type' => 'structure', 'members' => [ 'CiphertextBlob' => [ 'shape' => 'CiphertextType', ], 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'GenerateRandomRequest' => [ 'type' => 'structure', 'members' => [ 'NumberOfBytes' => [ 'shape' => 'NumberOfBytesType', ], ], ], 'GenerateRandomResponse' => [ 'type' => 'structure', 'members' => [ 'Plaintext' => [ 'shape' => 'PlaintextType', ], ], ], 'GetKeyPolicyRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'PolicyName', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'PolicyName' => [ 'shape' => 'PolicyNameType', ], ], ], 'GetKeyPolicyResponse' => [ 'type' => 'structure', 'members' => [ 'Policy' => [ 'shape' => 'PolicyType', ], ], ], 'GetKeyRotationStatusRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'GetKeyRotationStatusResponse' => [ 'type' => 'structure', 'members' => [ 'KeyRotationEnabled' => [ 'shape' => 'BooleanType', ], ], ], 'GetParametersForImportRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'WrappingAlgorithm', 'WrappingKeySpec', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'WrappingAlgorithm' => [ 'shape' => 'AlgorithmSpec', ], 'WrappingKeySpec' => [ 'shape' => 'WrappingKeySpec', ], ], ], 'GetParametersForImportResponse' => [ 'type' => 'structure', 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'ImportToken' => [ 'shape' => 'CiphertextType', ], 'PublicKey' => [ 'shape' => 'PlaintextType', ], 'ParametersValidTo' => [ 'shape' => 'DateType', ], ], ], 'GrantConstraints' => [ 'type' => 'structure', 'members' => [ 'EncryptionContextSubset' => [ 'shape' => 'EncryptionContextType', ], 'EncryptionContextEquals' => [ 'shape' => 'EncryptionContextType', ], ], ], 'GrantIdType' => [ 'type' => 'string', 'max' => 128, 'min' => 1, ], 'GrantList' => [ 'type' => 'list', 'member' => [ 'shape' => 'GrantListEntry', ], ], 'GrantListEntry' => [ 'type' => 'structure', 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'GrantId' => [ 'shape' => 'GrantIdType', ], 'Name' => [ 'shape' => 'GrantNameType', ], 'CreationDate' => [ 'shape' => 'DateType', ], 'GranteePrincipal' => [ 'shape' => 'PrincipalIdType', ], 'RetiringPrincipal' => [ 'shape' => 'PrincipalIdType', ], 'IssuingAccount' => [ 'shape' => 'PrincipalIdType', ], 'Operations' => [ 'shape' => 'GrantOperationList', ], 'Constraints' => [ 'shape' => 'GrantConstraints', ], ], ], 'GrantNameType' => [ 'type' => 'string', 'max' => 256, 'min' => 1, 'pattern' => '^[a-zA-Z0-9:/_-]+$', ], 'GrantOperation' => [ 'type' => 'string', 'enum' => [ 'Decrypt', 'Encrypt', 'GenerateDataKey', 'GenerateDataKeyWithoutPlaintext', 'ReEncryptFrom', 'ReEncryptTo', 'CreateGrant', 'RetireGrant', 'DescribeKey', ], ], 'GrantOperationList' => [ 'type' => 'list', 'member' => [ 'shape' => 'GrantOperation', ], ], 'GrantTokenList' => [ 'type' => 'list', 'member' => [ 'shape' => 'GrantTokenType', ], 'max' => 10, 'min' => 0, ], 'GrantTokenType' => [ 'type' => 'string', 'max' => 8192, 'min' => 1, ], 'ImportKeyMaterialRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'ImportToken', 'EncryptedKeyMaterial', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'ImportToken' => [ 'shape' => 'CiphertextType', ], 'EncryptedKeyMaterial' => [ 'shape' => 'CiphertextType', ], 'ValidTo' => [ 'shape' => 'DateType', ], 'ExpirationModel' => [ 'shape' => 'ExpirationModelType', ], ], ], 'ImportKeyMaterialResponse' => [ 'type' => 'structure', 'members' => [], ], 'IncorrectKeyMaterialException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidAliasNameException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidArnException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidCiphertextException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidGrantIdException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidGrantTokenException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidImportTokenException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidKeyUsageException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'InvalidMarkerException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'KMSInternalException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'KMSInvalidStateException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'KeyIdType' => [ 'type' => 'string', 'max' => 2048, 'min' => 1, ], 'KeyList' => [ 'type' => 'list', 'member' => [ 'shape' => 'KeyListEntry', ], ], 'KeyListEntry' => [ 'type' => 'structure', 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'KeyArn' => [ 'shape' => 'ArnType', ], ], ], 'KeyManagerType' => [ 'type' => 'string', 'enum' => [ 'AWS', 'CUSTOMER', ], ], 'KeyMetadata' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'AWSAccountId' => [ 'shape' => 'AWSAccountIdType', ], 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Arn' => [ 'shape' => 'ArnType', ], 'CreationDate' => [ 'shape' => 'DateType', ], 'Enabled' => [ 'shape' => 'BooleanType', ], 'Description' => [ 'shape' => 'DescriptionType', ], 'KeyUsage' => [ 'shape' => 'KeyUsageType', ], 'KeyState' => [ 'shape' => 'KeyState', ], 'DeletionDate' => [ 'shape' => 'DateType', ], 'ValidTo' => [ 'shape' => 'DateType', ], 'Origin' => [ 'shape' => 'OriginType', ], 'ExpirationModel' => [ 'shape' => 'ExpirationModelType', ], 'KeyManager' => [ 'shape' => 'KeyManagerType', ], ], ], 'KeyState' => [ 'type' => 'string', 'enum' => [ 'Enabled', 'Disabled', 'PendingDeletion', 'PendingImport', ], ], 'KeyUnavailableException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, 'fault' => true, ], 'KeyUsageType' => [ 'type' => 'string', 'enum' => [ 'ENCRYPT_DECRYPT', ], ], 'LimitExceededException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'LimitType' => [ 'type' => 'integer', 'max' => 1000, 'min' => 1, ], 'ListAliasesRequest' => [ 'type' => 'structure', 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Limit' => [ 'shape' => 'LimitType', ], 'Marker' => [ 'shape' => 'MarkerType', ], ], ], 'ListAliasesResponse' => [ 'type' => 'structure', 'members' => [ 'Aliases' => [ 'shape' => 'AliasList', ], 'NextMarker' => [ 'shape' => 'MarkerType', ], 'Truncated' => [ 'shape' => 'BooleanType', ], ], ], 'ListGrantsRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'Limit' => [ 'shape' => 'LimitType', ], 'Marker' => [ 'shape' => 'MarkerType', ], 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'ListGrantsResponse' => [ 'type' => 'structure', 'members' => [ 'Grants' => [ 'shape' => 'GrantList', ], 'NextMarker' => [ 'shape' => 'MarkerType', ], 'Truncated' => [ 'shape' => 'BooleanType', ], ], ], 'ListKeyPoliciesRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Limit' => [ 'shape' => 'LimitType', ], 'Marker' => [ 'shape' => 'MarkerType', ], ], ], 'ListKeyPoliciesResponse' => [ 'type' => 'structure', 'members' => [ 'PolicyNames' => [ 'shape' => 'PolicyNameList', ], 'NextMarker' => [ 'shape' => 'MarkerType', ], 'Truncated' => [ 'shape' => 'BooleanType', ], ], ], 'ListKeysRequest' => [ 'type' => 'structure', 'members' => [ 'Limit' => [ 'shape' => 'LimitType', ], 'Marker' => [ 'shape' => 'MarkerType', ], ], ], 'ListKeysResponse' => [ 'type' => 'structure', 'members' => [ 'Keys' => [ 'shape' => 'KeyList', ], 'NextMarker' => [ 'shape' => 'MarkerType', ], 'Truncated' => [ 'shape' => 'BooleanType', ], ], ], 'ListResourceTagsRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Limit' => [ 'shape' => 'LimitType', ], 'Marker' => [ 'shape' => 'MarkerType', ], ], ], 'ListResourceTagsResponse' => [ 'type' => 'structure', 'members' => [ 'Tags' => [ 'shape' => 'TagList', ], 'NextMarker' => [ 'shape' => 'MarkerType', ], 'Truncated' => [ 'shape' => 'BooleanType', ], ], ], 'ListRetirableGrantsRequest' => [ 'type' => 'structure', 'required' => [ 'RetiringPrincipal', ], 'members' => [ 'Limit' => [ 'shape' => 'LimitType', ], 'Marker' => [ 'shape' => 'MarkerType', ], 'RetiringPrincipal' => [ 'shape' => 'PrincipalIdType', ], ], ], 'MalformedPolicyDocumentException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'MarkerType' => [ 'type' => 'string', 'max' => 1024, 'min' => 1, 'pattern' => '[\\u0020-\\u00FF]*', ], 'NotFoundException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'NumberOfBytesType' => [ 'type' => 'integer', 'max' => 1024, 'min' => 1, ], 'OriginType' => [ 'type' => 'string', 'enum' => [ 'AWS_KMS', 'EXTERNAL', ], ], 'PendingWindowInDaysType' => [ 'type' => 'integer', 'max' => 365, 'min' => 1, ], 'PlaintextType' => [ 'type' => 'blob', 'max' => 4096, 'min' => 1, 'sensitive' => true, ], 'PolicyNameList' => [ 'type' => 'list', 'member' => [ 'shape' => 'PolicyNameType', ], ], 'PolicyNameType' => [ 'type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '[\\w]+', ], 'PolicyType' => [ 'type' => 'string', 'max' => 131072, 'min' => 1, 'pattern' => '[\\u0009\\u000A\\u000D\\u0020-\\u00FF]+', ], 'PrincipalIdType' => [ 'type' => 'string', 'max' => 256, 'min' => 1, ], 'PutKeyPolicyRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'PolicyName', 'Policy', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'PolicyName' => [ 'shape' => 'PolicyNameType', ], 'Policy' => [ 'shape' => 'PolicyType', ], 'BypassPolicyLockoutSafetyCheck' => [ 'shape' => 'BooleanType', ], ], ], 'ReEncryptRequest' => [ 'type' => 'structure', 'required' => [ 'CiphertextBlob', 'DestinationKeyId', ], 'members' => [ 'CiphertextBlob' => [ 'shape' => 'CiphertextType', ], 'SourceEncryptionContext' => [ 'shape' => 'EncryptionContextType', ], 'DestinationKeyId' => [ 'shape' => 'KeyIdType', ], 'DestinationEncryptionContext' => [ 'shape' => 'EncryptionContextType', ], 'GrantTokens' => [ 'shape' => 'GrantTokenList', ], ], ], 'ReEncryptResponse' => [ 'type' => 'structure', 'members' => [ 'CiphertextBlob' => [ 'shape' => 'CiphertextType', ], 'SourceKeyId' => [ 'shape' => 'KeyIdType', ], 'KeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'RetireGrantRequest' => [ 'type' => 'structure', 'members' => [ 'GrantToken' => [ 'shape' => 'GrantTokenType', ], 'KeyId' => [ 'shape' => 'KeyIdType', ], 'GrantId' => [ 'shape' => 'GrantIdType', ], ], ], 'RevokeGrantRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'GrantId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'GrantId' => [ 'shape' => 'GrantIdType', ], ], ], 'ScheduleKeyDeletionRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'PendingWindowInDays' => [ 'shape' => 'PendingWindowInDaysType', ], ], ], 'ScheduleKeyDeletionResponse' => [ 'type' => 'structure', 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'DeletionDate' => [ 'shape' => 'DateType', ], ], ], 'Tag' => [ 'type' => 'structure', 'required' => [ 'TagKey', 'TagValue', ], 'members' => [ 'TagKey' => [ 'shape' => 'TagKeyType', ], 'TagValue' => [ 'shape' => 'TagValueType', ], ], ], 'TagException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'TagKeyList' => [ 'type' => 'list', 'member' => [ 'shape' => 'TagKeyType', ], ], 'TagKeyType' => [ 'type' => 'string', 'max' => 128, 'min' => 1, ], 'TagList' => [ 'type' => 'list', 'member' => [ 'shape' => 'Tag', ], ], 'TagResourceRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'Tags', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Tags' => [ 'shape' => 'TagList', ], ], ], 'TagValueType' => [ 'type' => 'string', 'max' => 256, 'min' => 0, ], 'UnsupportedOperationException' => [ 'type' => 'structure', 'members' => [ 'message' => [ 'shape' => 'ErrorMessageType', ], ], 'exception' => true, ], 'UntagResourceRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'TagKeys', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'TagKeys' => [ 'shape' => 'TagKeyList', ], ], ], 'UpdateAliasRequest' => [ 'type' => 'structure', 'required' => [ 'AliasName', 'TargetKeyId', ], 'members' => [ 'AliasName' => [ 'shape' => 'AliasNameType', ], 'TargetKeyId' => [ 'shape' => 'KeyIdType', ], ], ], 'UpdateKeyDescriptionRequest' => [ 'type' => 'structure', 'required' => [ 'KeyId', 'Description', ], 'members' => [ 'KeyId' => [ 'shape' => 'KeyIdType', ], 'Description' => [ 'shape' => 'DescriptionType', ], ], ], 'WrappingKeySpec' => [ 'type' => 'string', 'enum' => [ 'RSA_2048', ], ], ],];