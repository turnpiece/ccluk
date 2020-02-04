<?php
namespace Aws\WorkSpaces;

use Aws\AwsClient;

/**
 * Amazon WorkSpaces client.
 *
 * @method \Aws\Result associateIpGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise associateIpGroupsAsync(array $args = [])
 * @method \Aws\Result authorizeIpRules(array $args = [])
 * @method \GuzzleHttp\Promise\Promise authorizeIpRulesAsync(array $args = [])
 * @method \Aws\Result createIpGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createIpGroupAsync(array $args = [])
 * @method \Aws\Result createTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTagsAsync(array $args = [])
 * @method \Aws\Result createWorkspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createWorkspacesAsync(array $args = [])
 * @method \Aws\Result deleteIpGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteIpGroupAsync(array $args = [])
 * @method \Aws\Result deleteTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \Aws\Result describeIpGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeIpGroupsAsync(array $args = [])
 * @method \Aws\Result describeTags(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeTagsAsync(array $args = [])
 * @method \Aws\Result describeWorkspaceBundles(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeWorkspaceBundlesAsync(array $args = [])
 * @method \Aws\Result describeWorkspaceDirectories(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeWorkspaceDirectoriesAsync(array $args = [])
 * @method \Aws\Result describeWorkspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeWorkspacesAsync(array $args = [])
 * @method \Aws\Result describeWorkspacesConnectionStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeWorkspacesConnectionStatusAsync(array $args = [])
 * @method \Aws\Result disassociateIpGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disassociateIpGroupsAsync(array $args = [])
 * @method \Aws\Result modifyWorkspaceProperties(array $args = [])
 * @method \GuzzleHttp\Promise\Promise modifyWorkspacePropertiesAsync(array $args = [])
 * @method \Aws\Result modifyWorkspaceState(array $args = [])
 * @method \GuzzleHttp\Promise\Promise modifyWorkspaceStateAsync(array $args = [])
 * @method \Aws\Result rebootWorkspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise rebootWorkspacesAsync(array $args = [])
 * @method \Aws\Result rebuildWorkspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise rebuildWorkspacesAsync(array $args = [])
 * @method \Aws\Result revokeIpRules(array $args = [])
 * @method \GuzzleHttp\Promise\Promise revokeIpRulesAsync(array $args = [])
 * @method \Aws\Result startWorkspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startWorkspacesAsync(array $args = [])
 * @method \Aws\Result stopWorkspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopWorkspacesAsync(array $args = [])
 * @method \Aws\Result terminateWorkspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise terminateWorkspacesAsync(array $args = [])
 * @method \Aws\Result updateRulesOfIpGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRulesOfIpGroupAsync(array $args = [])
 */
class WorkSpacesClient extends AwsClient {}