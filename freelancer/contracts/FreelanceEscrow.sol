// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

contract FreelanceEscrow {
    struct MilestoneEscrow {
        uint256 milestoneId;
        address client;
        address freelancer;
        uint256 amount;
        bool funded;
        bool released;
    }

    mapping(uint256 => MilestoneEscrow) public escrows;
    uint256 public totalLocked;
    uint256 public totalReleased;

    event MilestoneFunded(uint256 indexed milestoneId, address indexed client, uint256 amount, uint256 timestamp);
    event MilestoneReleased(uint256 indexed milestoneId, address indexed freelancer, uint256 amount, uint256 timestamp);

    function depositMilestone(uint256 _milestoneId, address _freelancer) external payable {
        require(msg.value > 0, "Deposit must be > 0");
        require(!escrows[_milestoneId].funded, "Already funded");

        escrows[_milestoneId] = MilestoneEscrow({
            milestoneId: _milestoneId,
            client: msg.sender,
            freelancer: _freelancer,
            amount: msg.value,
            funded: true,
            released: false
        });

        totalLocked += msg.value;
        emit MilestoneFunded(_milestoneId, msg.sender, msg.value, block.timestamp);
    }

    function releaseMilestone(uint256 _milestoneId) external {
        MilestoneEscrow storage item = escrows[_milestoneId];
        require(item.funded, "Not funded");
        require(!item.released, "Already released");

        item.released = true;
        totalLocked -= item.amount;
        totalReleased += item.amount;

        payable(item.freelancer).transfer(item.amount);
        emit MilestoneReleased(_milestoneId, item.freelancer, item.amount, block.timestamp);
    }
}