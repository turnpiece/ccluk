<?php
namespace Aws\CodeDeploy;

use Aws\AwsClient;

/**
 * This client is used to interact with AWS CodeDeploy
 *
 * @method \Aws\Result addTagsToOnPremisesInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsToOnPremisesInstancesAsync(array $args = [])
 * @method \Aws\Result batchGetApplicationRevisions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetApplicationRevisionsAsync(array $args = [])
 * @method \Aws\Result batchGetApplications(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetApplicationsAsync(array $args = [])
 * @method \Aws\Result batchGetDeploymentGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetDeploymentGroupsAsync(array $args = [])
 * @method \Aws\Result batchGetDeploymentInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetDeploymentInstancesAsync(array $args = [])
 * @method \Aws\Result batchGetDeploymentTargets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetDeploymentTargetsAsync(array $args = [])
 * @method \Aws\Result batchGetDeployments(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetDeploymentsAsync(array $args = [])
 * @method \Aws\Result batchGetOnPremisesInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetOnPremisesInstancesAsync(array $args = [])
 * @method \Aws\Result continueDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise continueDeploymentAsync(array $args = [])
 * @method \Aws\Result createApplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \Aws\Result createDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDeploymentAsync(array $args = [])
 * @method \Aws\Result createDeploymentConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDeploymentConfigAsync(array $args = [])
 * @method \Aws\Result createDeploymentGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDeploymentGroupAsync(array $args = [])
 * @method \Aws\Result deleteApplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \Aws\Result deleteDeploymentConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDeploymentConfigAsync(array $args = [])
 * @method \Aws\Result deleteDeploymentGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDeploymentGroupAsync(array $args = [])
 * @method \Aws\Result deleteGitHubAccountToken(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteGitHubAccountTokenAsync(array $args = [])
 * @method \Aws\Result deregisterOnPremisesInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deregisterOnPremisesInstanceAsync(array $args = [])
 * @method \Aws\Result getApplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getApplicationAsync(array $args = [])
 * @method \Aws\Result getApplicationRevision(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getApplicationRevisionAsync(array $args = [])
 * @method \Aws\Result getDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeploymentAsync(array $args = [])
 * @method \Aws\Result getDeploymentConfig(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeploymentConfigAsync(array $args = [])
 * @method \Aws\Result getDeploymentGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeploymentGroupAsync(array $args = [])
 * @method \Aws\Result getDeploymentInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeploymentInstanceAsync(array $args = [])
 * @method \Aws\Result getDeploymentTarget(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeploymentTargetAsync(array $args = [])
 * @method \Aws\Result getOnPremisesInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getOnPremisesInstanceAsync(array $args = [])
 * @method \Aws\Result listApplicationRevisions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listApplicationRevisionsAsync(array $args = [])
 * @method \Aws\Result listApplications(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \Aws\Result listDeploymentConfigs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentConfigsAsync(array $args = [])
 * @method \Aws\Result listDeploymentGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentGroupsAsync(array $args = [])
 * @method \Aws\Result listDeploymentInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentInstancesAsync(array $args = [])
 * @method \Aws\Result listDeploymentTargets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentTargetsAsync(array $args = [])
 * @method \Aws\Result listDeployments(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentsAsync(array $args = [])
 * @method \Aws\Result listGitHubAccountTokenNames(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listGitHubAccountTokenNamesAsync(array $args = [])
 * @method \Aws\Result listOnPremisesInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listOnPremisesInstancesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putLifecycleEventHookExecutionStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putLifecycleEventHookExecutionStatusAsync(array $args = [])
 * @method \Aws\Result registerApplicationRevision(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerApplicationRevisionAsync(array $args = [])
 * @method \Aws\Result registerOnPremisesInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerOnPremisesInstanceAsync(array $args = [])
 * @method \Aws\Result removeTagsFromOnPremisesInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsFromOnPremisesInstancesAsync(array $args = [])
 * @method \Aws\Result skipWaitTimeForInstanceTermination(array $args = [])
 * @method \GuzzleHttp\Promise\Promise skipWaitTimeForInstanceTerminationAsync(array $args = [])
 * @method \Aws\Result stopDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopDeploymentAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateApplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateApplicationAsync(array $args = [])
 * @method \Aws\Result updateDeploymentGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDeploymentGroupAsync(array $args = [])
 */
class CodeDeployClient extends AwsClient {}