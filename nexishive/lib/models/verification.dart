import 'package:google_generative_ai/google_generative_ai.dart';

const GOOGLE_API_KEY = "AIzaSyD2yaEh1txHcrUfzlzSgb-gRwvaz8istVc";

class VerifyUser {
  Future<String?> questionGenerator(String topic) async {
    try {
      final apiKey = GOOGLE_API_KEY;
      final model = GenerativeModel(model: 'gemini-pro', apiKey: apiKey);
      final content = [
        Content.text(
          'Generate a very basic question for this topic $topic that every member of this topic should know, only provide the question dont include any other unnecessary things',
        )
      ];
      final question = await model.generateContent(content);
      print(question.text);
      return question.text;
    } catch (e) {
      return e.toString();
    }
  }

  Future<String?> isCorrect(String answer, String question) async {
    // Access your API key as an environment variable (see "Set up your API key" above)
    try {
      final apiKey = GOOGLE_API_KEY;
      // For text-only input, use the gemini-pro model
      final model = GenerativeModel(model: 'gemini-pro', apiKey: apiKey);
      final content = [
        Content.text(
            'The question is $question and the answer is $answer. Is the answer correct ? if yes then answer yes else answer no.')
      ];
      final response = await model.generateContent(content);
      print(response.text);
      return response.text;
    } catch (e) {
      return e.toString();
    }
  }
}
