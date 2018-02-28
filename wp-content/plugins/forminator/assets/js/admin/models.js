(function ($) {
	define([
		'admin/models/builder-model',
		'admin/models/field-model',
		'admin/models/wrapper-model',
		'admin/models/condition-model',
		'admin/models/poll-model',
		'admin/models/knowledge-model',
		'admin/models/nowrong-model',
		'admin/models/answer-model',
		'admin/models/question-model',
		'admin/models/result-model',
		'admin/models/quiz-model',
		'admin/models/login-register-model',
		'admin/models/fields-collection',
		'admin/models/wrappers-collection',
		'admin/models/answers-collection',
		'admin/models/questions-collection',
		'admin/models/results-collection',
		'admin/models/conditions-collection'
	], function(
		BuilderModel,
		FieldModel,
		WrapperModel,
		ConditionModel,
		PollModel,
		KnowledgeModel,
		NoWrongModel,
		AnswerModel,
		QuestionModel,
		ResultModel,
		QuizModel,
		LoginRegisterModel,
		FieldsCollection,
		WrappersCollection,
		AnswersCollection,
		QuestionsCollection,
		ResultsCollection,
		ConditionsCollection
	) {
		return {
			"Models": {
				Builder: BuilderModel,
				Poll: PollModel,
				Knowledge: KnowledgeModel,
				NoWrong: NoWrongModel,
				Answer: AnswerModel,
				Question: QuestionModel,
				Result: ResultModel,
				Fields: FieldModel,
				Wrapper: WrapperModel,
				Condition: ConditionModel,
				Quiz: QuizModel,
				LoginRegister: LoginRegisterModel
			},
			"Collections": {
				Fields: FieldsCollection,
				Wrappers: WrappersCollection,
				Answers: AnswersCollection,
				Questions: QuestionsCollection,
				Results: ResultsCollection,
				Conditions: ConditionsCollection
			}
		};
	});
})(jQuery);
