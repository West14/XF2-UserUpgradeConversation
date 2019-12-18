<?php

namespace West\UserUpgradeConversation\XF\Service\User;

class Upgrade extends XFCP_Upgrade
{
	public function upgrade()
    {
        $parent = parent::upgrade();

        if ($parent instanceof \XF\Entity\UserUpgradeActive)
        {
            $this->sendConversation();
        }

        return $parent;
    }

    protected function sendConversation()
    {
        /** @var \XF\Service\Conversation\Creator $creator */
        $creator = $this->service(
            'XF:Conversation\Creator',
            $starterUser = $this->findOne('XF:User', [
                'username' => $this->app->options()->wUucStarterUser
            ])
        );
        $creator->setRecipientsTrusted($this->user);
        $creator->setIsAutomated();
        $creator->setOptions([
            'open_invite' => false
        ]);
        $creator->setContent(
            \XF::phrase('w_uuc_conversation_title')->render(),
            \XF::phrase('w_uuc_conversation_message', [
                'username' => $this->user->username,
                'upgrade_title' => $this->userUpgrade->title,
                'end_date' => $this->endDate
                    ? $this->app->language($this->user->language_id)->dateTime($this->endDate)
                    : \XF::phrase('never')->render()
            ])->render()
        );
        $creator->validate($errors);
        $creator->save();
    }
}