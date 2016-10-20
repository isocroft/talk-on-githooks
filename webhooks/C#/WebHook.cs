

public class GitHubHandler : WebHookHandler
{



    public override Task ExecuteAsync(string receiver, WebHookHandlerContext context)
    {
        string action = context.Actions.First();
        JObject data = context.GetDataOrDefault<JObject>();
  
        return Task.FromResult(true);
    }

    
}